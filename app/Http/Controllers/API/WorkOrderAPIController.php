<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateWorkOrderAPIRequest;
use App\Http\Requests\API\UpdateWorkOrderAPIRequest;
use App\Http\Resources\WorkOrderDetailResource;
use App\Http\Resources\WorkOrderResource;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\GroupTask;
use App\Models\Location;
use App\Models\LocationAddress;
use App\Models\PurchaseOrder;
use App\Models\Task;
use App\Models\WorkInstruction;
use App\Models\WorkOrder;
use App\Models\WorkOrderEmployee;
use App\Models\WorkOrderHasAddress;
use App\Models\WorkOrderHasGroupTask;
use App\Models\WorkOrderHasTask;
use App\Models\WorkOrderHasWorkInstruction;
use App\Repositories\WorkOrderEmployeeRepository;
use App\Repositories\WorkOrderHasGroupTaskRepository;
use App\Repositories\WorkOrderRepository;
use App\Traits\MapTask;
use Carbon\Carbon;
use iio\libmergepdf\Merger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDF;
use Response;

/**
 * Class WorkOrderController
 * @package App\Http\Controllers\API
 */
class WorkOrderAPIController extends AppBaseController
{
    /** @var  WorkOrderRepository */
    private $workOrderRepository, $workOrderEmployeeRepository, $workOrderWorkInstructionRepository;

    public function __construct(WorkOrderRepository $workOrderRepo, WorkOrderEmployeeRepository $workOrderEmployeeRepo, WorkOrderHasWorkInstruction $workOrderWorkInstructionRepo)
    {
        $this->workOrderRepository = $workOrderRepo;
        $this->workOrderEmployeeRepository = $workOrderEmployeeRepo;
        $this->workOrderWorkInstructionRepository = $workOrderWorkInstructionRepo;
    }

    /**
     * Display a listing of the WorkOrder.
     * GET|HEAD /workOrders
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->authorize('work_order_view');

        list($input, $current, $pageSize) = $this->getInput($request);

        // if (isset($input['search']) || isset($input['date']) || isset($input['po']) || isset($input['employees_id'])) {

        $query = WorkOrder::query();
        if (isset($input['search'])) {
            // location query

            $query = WorkOrder::where('person_in_charge', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('name', 'LIKE', '%' . $input['search'] . '%')
                ->orWhereHas('purchaseOrder', function ($query) use ($input) {
                    $query->whereHas('location', function ($query) use ($input) {
                        $query->where('code', 'LIKE', '%' . $input['search'] . '%');
                    });
                });
        }

        if (isset($input['date'])) {
            $query->where('started_at', '<=', $input['date'])
                ->where('ended_at', '>=', $input['date']);
        }

        if (isset($input['po'])) {
            $query->where('purchase_order_id', $input['po']);
        }

        if (isset($input['status'])) {
            if (is_array($input['status'])) {
                $query->whereIn('status', $input['status']);
            } else {
                $query->where('status', $input['status']);
            }
        }

        if (isset($input['employees_id'])) {
            $employeesId = json_decode($input['employees_id']);
            $query->whereHas('employees', function ($query) use ($employeesId) {
                $query->whereIn('work_orders_has_employees.employee_id', $employeesId);
            });
        }

        $query = $query->orderBy('work_orders.updated_at', 'desc');

        $total = $query->get()->count();

        $workOrders = $query->skip($current)
            ->take($pageSize)
            ->get();

        // } else if (isset($input['date'])) {
        //     $parsedDate = Carbon::parse(str_replace('"', '', $input['date']));
        //     $query = WorkOrder::where('started_at', '<=', $parsedDate->endOfDay())
        //         ->where('ended_at', '>=', $parsedDate->startOfDay());

        //     $workOrders = $query->skip($current)
        //         ->take($pageSize)
        //         ->get();

        //     $total = $query->get()->count();

        // } else {

        //     $workOrders = $this->workOrderRepository->all(
        //         $input,
        //         $current,
        //         $pageSize
        //     );

        //     $total = count($this->workOrderRepository->all(
        //         $input
        //     ));
        // }

        return $this->sendResponse([
            'data'  => WorkOrderResource::collection($workOrders),
            'total' => $total,
        ], 'Work Orders retrieved successfully');
    }

    /**
     * Store a newly created WorkOrder in storage.
     * POST /workOrders
     *
     * @param CreateWorkOrderAPIRequest $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->authorize('work_order_create');

        $input = $request->all();

        DB::beginTransaction();
        try {
            $workOrder = $this->workOrderRepository->create($input);

            foreach ($input['group_task_id'] as $groupTaskId) {
                WorkOrderHasGroupTask::create([
                    'work_order_id' => $workOrder->id,
                    'group_task_id' => $groupTaskId,
                    'status'        => WorkOrderHasGroupTask::ACTIVE,
                ]);

                // create PO task price data
                $groupTask = GroupTask::find($groupTaskId);
                // set GT status to taken
                $groupTask->update([
                    'status' => GroupTask::TAKEN,
                ]);
                $tasks = $groupTask->tasks;
                if (count($tasks)) {
                    foreach ($tasks as $task) {
                        // initialize qty, unit price as stored in Task model
                        WorkOrderHasTask::create([
                            'work_order_id'   => $workOrder->id,
                            'group_task_id'   => $groupTaskId,
                            'group_task_name' => $groupTask->display_name,
                            'task_name'       => $task->name,
                            'task_id'         => $task->id,
                            'task_no'         => $task->task_no,
                            'status'          => WorkOrderHasTask::ACTIVE,
                            'qty'             => $task->qty,
                            'unit'            => $task->unit,
                            'unit_price'      => $task->unit_price,
                            'total_price'     => $task->total_price,
                        ]);
                        // update total price to PO level
                        $workOrder->update([
                            'total_price' => $tasks->sum('total_price'),
                        ]);
                    }
                }
            }

            foreach ($input['employees_id'] as $employeeId) {
                $employee = Employee::find($employeeId);
                $this->workOrderEmployeeRepository->create([
                    'work_order_id' => $workOrder->id,
                    'employee_id'   => $employeeId,
                    'status'        => WorkOrderEmployee::ACTIVE,
                ]);
            }

            if (isset($input['work_instructions_id'])) {
                foreach ($input['work_instructions_id'] as $workInstructionId) {
                    $WorkInstruction = WorkInstruction::find($workInstructionId);
                    $this->workOrderWorkInstructionRepository->create([
                        'work_order_id'       => $workOrder->id,
                        'work_instruction_id' => $workInstructionId,
                        'status'              => WorkOrderHasWorkInstruction::ACTIVE,
                    ]);
                }
            }

            if (isset($input['is_ewo']) && $input['is_ewo']) {
                MapTask::mapTasks('work_order_id', $workOrder->id, $input['group_tasks'], 'App\Model\WorkOrderHasGroupTask', 'App\Model\WorkOrderHasTask');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendApiError($e->getMessage());
        }

        return $this->sendResponse(WorkOrderResource::make($workOrder), 'Work Order saved successfully');
    }

    /**
     * Display the specified WorkOrder.
     * GET|HEAD /workOrders/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('work_order_view');

        /** @var WorkOrder $workOrder */
        $workOrder = $this->workOrderRepository->find($id, ['purchaseOrder', 'groupTasks', 'employees', 'workInstructions', 'location', 'addresses', 'taskInfo']);

        if (empty($workOrder)) {
            return $this->sendError('Work Order not found');
        }

        return $this->sendResponse(WorkOrderDetailResource::make($workOrder), 'Work Order retrieved successfully');
    }

    /**
     * Update the specified WorkOrder in storage.
     * PUT/PATCH /workOrders/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $this->authorize('work_order_update');

        $input = $request->all();

        /** @var WorkOrder $workOrder */
        $workOrder = $this->workOrderRepository->find($id);

        if (empty($workOrder)) {
            return $this->sendError('Work Order not found');
        }

        // status control

        if ($request->status == WorkOrder::COMPLETED) {
            if (!count($workOrder->signedCompletionSummaries) && !count($workOrder->completionSummaryPdfs)) {
                return $this->sendError('Update status fail. Reason: Signed completion summary could not be found.');
            }
        }

        // DB::beginTransaction();
        // try {

        $workOrder = $this->workOrderRepository->update($input, $id);

        $oriGroupTask = $workOrder->groupTasks->pluck('id')->toArray();

        $deleteGroupTask = array_diff($oriGroupTask, $input['group_task_id']);
        $createGroupTask = array_diff($input['group_task_id'], $oriGroupTask);

        foreach ($deleteGroupTask as $groupTaskId) {
            GroupTask::find($groupTaskId)->update([
                'status' => GroupTask::ACTIVE,
            ]);
            WorkOrderHasGroupTask::where('work_order_id', $id)->where('group_task_id', $groupTaskId)->forceDelete();
            WorkOrderHasTask::where('work_order_id', $id)->where('group_task_id', $groupTaskId)->delete();

        }
        foreach ($createGroupTask as $groupTaskId) {
            GroupTask::find($groupTaskId)->update([
                'status' => GroupTask::TAKEN,
            ]);
            WorkOrderHasGroupTask::create(
                [
                    'work_order_id' => $id,
                    'group_task_id' => $groupTaskId,
                    'status'        => WorkOrderEmployee::ACTIVE,
                ]
            );
            // create PO task price data
            $groupTask = GroupTask::find($groupTaskId);
            $tasks = $groupTask->tasks;
            if (count($tasks)) {
                foreach ($tasks as $task) {
                    // initialize qty, unit price as stored in Task model
                    WorkOrderHasTask::create([
                        'work_order_id'   => $id,
                        'group_task_id'   => $groupTaskId,
                        'group_task_name' => $groupTask->display_name,
                        'task_name'       => $task->name,
                        'task_id'         => $task->id,
                        'task_no'         => $task->task_no,
                        'status'          => WorkOrderHasTask::ACTIVE,
                        'qty'             => $task->qty,
                        'unit'            => $task->unit,
                        'unit_price'      => $task->unit_price,
                        'total_price'     => $task->total_price,
                    ]);
                }
            }
        }

        if ($request->task_info) {
            foreach ($request->task_info as $groupTaskId => $groupTask) {
                foreach ($groupTask as $taskInfo) {
                    if (!isset($taskInfo['task_id'])) {
                        // newly added task
                        $groupTask = GroupTask::find($groupTaskId);
                        $task = Task::create([
                            'group_task_id' => $groupTaskId,
                            'name'          => $taskInfo['task_name'],
                            'qty'           => $taskInfo['qty'],
                            'task_no'       => $taskInfo['task_no'] ?? null,
                            'unit'          => $taskInfo['unit'],
                            'unit_price'    => 0,
                            'total_price'   => 0,
                        ]);
                        WorkOrderHasTask::create([
                            'work_order_id'   => $id,
                            'group_task_id'   => $groupTaskId,
                            'group_task_name' => $groupTask->group_task_name,
                            'task_id'         => $task->id,
                            'task_name'       => $taskInfo['task_name'],
                            'task_no'         => $taskInfo['task_no'] ?? null,
                            'qty'             => $taskInfo['qty'],
                            'unit'            => $taskInfo['unit'],
                            'unit_price'      => 0,
                            'total_price'     => 0,
                        ]);

                    } else {
                        WorkOrderHasTask::where('work_order_id', $taskInfo['work_order_id'])
                            ->where('group_task_id', $groupTaskId)
                            ->where('task_id', $taskInfo['task_id'])
                            ->update([
                                'task_name'   => $taskInfo['task_name'],
                                'qty'         => $taskInfo['qty'],
                                'task_no'     => $taskInfo['task_no'],
                                'unit'        => $taskInfo['unit'],
                                'unit_price'  => $taskInfo['unit_price'],
                                'total_price' => $taskInfo['qty'] * $taskInfo['unit_price'],
                            ]);
                    }
                }
            }
        }

        $oriEmployee = $workOrder->employees->pluck('id')->toArray();

        $deleteEmployee = array_diff($oriEmployee, $input['employees_id']);
        $createEmployee = array_diff($input['employees_id'], $oriEmployee);

        foreach ($deleteEmployee as $employeeId) {
            // $employee = Employee::find($employeeId);
            WorkOrderEmployee::where('work_order_id', $id)->where('employee_id', $employeeId)->forceDelete();
        }
        foreach ($createEmployee as $employeeId) {
            // $employee = Employee::find($employeeId);
            WorkOrderEmployee::create(
                [
                    'work_order_id' => $id,
                    'employee_id'   => $employeeId,
                    'status'        => WorkOrderEmployee::ACTIVE,
                ]
            );
        }

        $oriInstruction = $workOrder->workInstructions->pluck('id')->toArray();

        $deleteInstruction = array_diff($oriInstruction, $input['work_instructions_id']);
        $createInstruction = array_diff($input['work_instructions_id'], $oriInstruction);

        foreach ($deleteInstruction as $instructionId) {

            WorkOrderHasWorkInstruction::where('work_order_id', $id)->where('work_instruction_id', $instructionId)->forceDelete();
        }
        foreach ($createInstruction as $instructionId) {

            WorkOrderHasWorkInstruction::create(
                [
                    'work_order_id'       => $id,
                    'work_instruction_id' => $instructionId,
                    'status'              => WorkOrderHasWorkInstruction::ACTIVE,
                ]
            );
        }

        if (isset($input['is_ewo']) && $input['is_ewo']) {
            MapTask::mapTasks('work_order_id', $workOrder->id, $input['group_tasks'], 'App\Model\WorkOrderHasGroupTask', 'App\Model\WorkOrderHasTask');
        }
        //     DB::commit();
        // } catch (\Exception$e) {
        //     DB::rollBack();
        //     return $this->sendApiError($e->getMessage());
        // }

        $workOrder = $this->workOrderRepository->find($id, ['purchaseOrder', 'groupTasks', 'employees', 'workInstructions', 'location', 'addresses']);
        return $this->sendResponse(WorkOrderDetailResource::make($workOrder), 'Work Order updated successfully');
    }

    /**
     * Remove the specified WorkOrder from storage.
     * DELETE /workOrders/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        $this->authorize('work_order_delete');

        /** @var WorkOrder $workOrder */
        $workOrder = $this->workOrderRepository->find($id);

        if (empty($workOrder)) {
            return $this->sendError('Work Order not found');
        }

        $workOrder->delete();

        return $this->sendSuccess('Work Order deleted successfully');
    }

    public function genReport($id, Request $request)
    {
        $input = $request->all();

        // validating length of result images: 4

        if (count($input['selectedImage']) < 1) {
            return $this->sendError("Please select result images.");
        }

        $workOrder = WorkOrder::find($id);

        $purchaseOrder = $workOrder->purchaseOrder;

        // make service report pdf

        $pdf1 = PDF::loadView('report.service-report', compact('workOrder', 'purchaseOrder'), [], [
            'format' => 'a5',
        ]);

        $ref = Str::uuid();

        if (!is_dir(public_path('service-report'))) {
            mkdir(public_path('service-report'));
        }
        \File::put(public_path('service-report') . '/' . $ref . '.pdf', $pdf1->Output($ref . '.pdf', "S"));

        $path1 = public_path('service-report') . '/' . $ref . '.pdf';

        Asset::create([
            'related_type'  => $this->workOrderRepository->model(),
            'related_id'    => $workOrder->id,
            'asset_type'    => WorkOrder::SERVICE_REPORT_PDF,
            'url'           => '/service-report/' . $ref . '.pdf',
            'resource_path' => public_path('service-report') . '/' . $ref . '.pdf',
            'file_size'     => 0,
            'file_name'     => $ref . '.pdf',
            'status'        => Asset::ACTIVE,
        ]);

        // make result image pdf

        // handle quot on null

        if (!count($workOrder->purchaseOrder->quotation)) {
            $quotUrl = "Quotation not generated";
        } else {
            $quotUrl = str_replace('.pdf', '', str_replace('/quotation/', '', $workOrder->purchaseOrder->quotation()->first()->url));
        }

        $resultImages = [];

        foreach ($input['selectedImage'] as $assetId) {
            array_push($resultImages, Asset::find($assetId));
        }

        $ref = Str::uuid();

        if (!is_dir(public_path('result-image-pdf'))) {
            mkdir(public_path('result-image-pdf'));
        }
        $pdf2 = \Barryvdh\DomPDF\Facade\Pdf::loadView('report.result-image', ['data' => compact('workOrder', 'resultImages', 'quotUrl')])
            ->save(public_path('result-image-pdf') . '/' . $ref . '.pdf');

        // merge as completion summary

        $path2 = public_path('result-image-pdf') . '/' . $ref . '.pdf';

        Asset::create([
            'related_type'  => $this->workOrderRepository->model(),
            'related_id'    => $workOrder->id,
            'asset_type'    => WorkOrder::RESULT_IMAGE_PDF,
            'url'           => '/result-image-pdf/' . $ref . '.pdf',
            'resource_path' => public_path('result-image-pdf') . '/' . $ref . '.pdf',
            'file_size'     => 0,
            'file_name'     => $ref . '.pdf',
            'status'        => Asset::ACTIVE,
        ]);

        $merger = new Merger;
        $merger->addFile($path2);
        $merger->addFile($path1);
        $createdPdf = $merger->merge();

        $ref = Str::uuid();

        if (!is_dir(public_path('completion-summary'))) {
            mkdir(public_path('completion-summary'));
        }
        \File::put(public_path('completion-summary') . '/' . $ref . '.pdf', $createdPdf);

        $path3 = asset('completion-summary/' . $ref . '.pdf');

        Asset::create([
            'related_type'  => $this->workOrderRepository->model(),
            'related_id'    => $workOrder->id,
            'asset_type'    => WorkOrder::COMPLETION_SUMMARY_PDF,
            'url'           => '/completion-summary/' . $ref . '.pdf',
            'resource_path' => public_path('completion-summary') . '/' . $ref . '.pdf',
            'file_size'     => 0,
            'file_name'     => $ref . '.pdf',
            'status'        => Asset::ACTIVE,
        ]);

        return $this->sendResponse(['file_path' => $path3], 'Service Report successfully generated.');

    }

    public function addressList($id)
    {
        $addresses = PurchaseOrder::find($id)->location->addresses;
        $list = isset($addresses) ? $addresses->pluck('address', 'id') : null;
        return response()->json($list);
    }

    public static function syncPivot()
    {
        $workOrders = WorkOrder::get();
        foreach ($workOrders as $wo) {
            if (isset($wo->groupTasks)) {
                foreach ($wo->groupTasks as $groupTask) {
                    if (isset($groupTask->tasks)) {
                        foreach ($groupTask->tasks as $task) {
                            WorkOrderHasTask::firstOrCreate([
                                'work_order_id' => $wo->id,
                                'group_task_id' => $groupTask->id,
                                'task_id'       => $task->id,
                            ], [
                                'group_task_name' => $groupTask->group_task_name,
                                'task_name'       => $task->name,
                                'task_no'         => $task->task_no,
                                'qty'             => $task->qty,
                                'unit'            => $task->unit,
                                'unit_price'      => $task->unit_price,
                                'total_price'     => $task->total_price,
                            ]);
                        }
                    }
                }
            }
            // sync total_price to WO
            $wo->update([
                'total_price' => $wo->taskInfo()->sum('total_price'),
            ]);

        }
    }
}
