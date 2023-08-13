<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\PurchaseOrderResource;
use App\Http\Resources\SubcontractorTaskPriceResource;
use App\Models\Asset;
use App\Models\Client;
use App\Models\ContactPerson;
use App\Models\GroupTask;
use App\Models\ProductPriceHistory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderHasClient;
use App\Models\PurchaseOrderHasContactPerson;
use App\Models\PurchaseOrderHasGroupTask;
use App\Models\PurchaseOrderHasSubcontractor;
use App\Models\PurchaseOrderHasSubcontractorTask;
use App\Models\PurchaseOrderHasSupplier;
use App\Models\PurchaseOrderHasTask;
use App\Models\Subcontractor;
use App\Models\SubcontractorHasTask;
use App\Models\SubcontractorPOData;
use App\Models\SubcontractorTaskPrice;
use App\Models\Supplier;
use App\Models\SupplierPOData;
use App\Models\SupplierPOHasTask;
use App\Models\Task;
use App\Models\WorkOrderHasGroupTask;
use App\Repositories\OutlineAgreementRepository;
use App\Repositories\PurchaseOrderRepository;
use App\Traits\M2M;
use App\Traits\MapTaskNew;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDF;
use Response;

/**
 * Class PurchaseOrderController
 * @package App\Http\Controllers\API
 */
class PurchaseOrderAPIController extends AppBaseController
{
    /** @var  PurchaseOrderRepository */
    private $purchaseOrderRepository, $outlineAgreementRepository;

    public function __construct(PurchaseOrderRepository $purchaseOrderRepo, OutlineAgreementRepository $outlineAgreementRepo)
    {
        $this->purchaseOrderRepository = $purchaseOrderRepo;
        $this->outlineAgreementRepository = $outlineAgreementRepo;
    }

    /**
     * Display a listing of the PurchaseOrder.
     * GET|HEAD /purchaseOrders
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->authorize('purchase_order_view');

        list($input, $current, $pageSize) = $this->getInput($request);

        $checkEmpty = $request->all();
        if (empty($checkEmpty)) {
            return response()->json(PurchaseOrder::get()->mapWithKeys(function ($po) {
                return [$po->id => $po->displayName];
            })
            );
        }

        if (isset($input['status']) && $input['status'] == 'all') {
            return response()->json(PurchaseOrder::get()->mapWithKeys(function ($po) {
                return [$po->id => $po->displayName];
            })
            );

        }

        if (isset($input['search'])) {
            $query = PurchaseOrder::where('po_number', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('name', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('contact_name', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('quot_ref', 'LIKE', '%' . $input['search'] . '%');

            $total = $query->get()->count();

            $purchaseOrders = $query->skip($current)
                ->take($pageSize)
                ->get();

        } else {

            $purchaseOrders = $this->purchaseOrderRepository->all(
                $input,
                $current,
                $pageSize
            );

            $total = count($this->purchaseOrderRepository->all(
                $input
            ));
        }

        return $this->sendResponse([
            'data'  => PurchaseOrderResource::collection($purchaseOrders),
            'total' => $total,
        ], 'Purchase Orders retrieved successfully');
    }

    /**
     * Store a newly created PurchaseOrder in storage.
     * POST /purchaseOrders
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->authorize('purchase_order_create');

        $input = $request->all();

        $request->validate([
            'location_id' => 'required',
            'client_id'   => 'required',
        ]);

        DB::beginTransaction();
        try {
            // unset OA id if is not contract
            if (!$request->is_contract && isset($request->outline_agreement_id)) {
                unset($input['outline_agreement_id']);
            }
            $purchaseOrder = $this->purchaseOrderRepository->create($input);

            if (isset($request->client_id)) {
                $client = Client::find($request->client_id);
                PurchaseOrderHasClient::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'client_id'         => $request->client_id,
                    'status'            => PurchaseOrderHasClient::ACTIVE,
                ]);
            }

            if (isset($request->suppliers_id)) {
                M2M::storeM2M($purchaseOrder->id, $request->suppliers_id, 'App\Models\PurchaseOrderHasSupplier');
            }
            if (isset($request->subcontractors_id)) {
                M2M::storeM2M($purchaseOrder->id, $request->subcontractors_id, 'App\Models\PurchaseOrderHasSubcontractor');
            }

            if (isset($input['contact_people_id'])) {
                foreach ($input['contact_people_id'] as $contactId) {
                    PurchaseOrderHasContactPerson::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'contact_person_id' => $contactId,
                        'status'            => PurchaseOrderHasContactPerson::ACTIVE,
                    ]);
                }
            }

            if ($request->is_contract) {
                if (isset($input['group_task_id'])) {
                    foreach ($input['group_task_id'] as $groupTaskId) {
                        PurchaseOrderHasGroupTask::create([
                            'purchase_order_id' => $purchaseOrder->id,
                            'group_task_id'     => $groupTaskId,
                            'status'            => PurchaseOrderHasGroupTask::ACTIVE,
                            'actual_qty'        => GroupTask::find($groupTaskId)->qty,
                            'actual_price'      => GroupTask::find($groupTaskId)->total_price,

                        ]);
                        // create PO task price data
                        $groupTask = GroupTask::find($groupTaskId);
                        $tasks = $groupTask->tasks;
                        if (count($tasks)) {
                            foreach ($tasks as $task) {
                                // initialize qty, unit price as stored in Task model
                                PurchaseOrderHasTask::create([
                                    'purchase_order_id' => $purchaseOrder->id,
                                    'group_task_id'     => $groupTaskId,
                                    'group_task_name'   => $groupTask->display_name,
                                    'task_name'         => $task->name,
                                    'task_id'           => $task->id,
                                    'task_no'           => $task->task_no,
                                    'status'            => PurchaseOrderHasTask::ACTIVE,
                                    'qty'               => $task->qty,
                                    'unit'              => $task->unit,
                                    'unit_price'        => $task->unit_price,
                                    'total_price'       => $task->total_price,
                                ]);
                                PurchaseOrderHasSubcontractorTask::create([
                                    'purchase_order_id' => $purchaseOrder->id,
                                    'group_task_id'     => $groupTaskId,
                                    'group_task_name'   => $groupTask->display_name,
                                    'task_name'         => $task->name,
                                    'task_id'           => $task->id,
                                    'task_no'           => $task->task_no,
                                    'status'            => PurchaseOrderHasTask::ACTIVE,
                                    'qty'               => $task->qty,
                                    'unit'              => $task->unit,
                                    'unit_price'        => $task->unit_price,
                                    'total_price'       => $task->total_price,
                                ]);
                            }
                            // update total price to PO level
                            $purchaseOrder->update([
                                'total_price' => $tasks->sum('total_price'),
                            ]);
                        }

                    }

                }
                // MapTaskNew::mapTasks('purchase_order_id', $purchaseOrder->id, $input['group_tasks'], 'App\Models\PurchaseOrderHasGroupTask');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendApiError($e->getMessage());
        }

        return $this->sendResponse(PurchaseOrderResource::make($purchaseOrder), 'Purchase Order saved successfully');
    }

    /**
     * Display the specified PurchaseOrder.
     * GET|HEAD /purchaseOrders/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('purchase_order_view');

        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $this->purchaseOrderRepository->find($id, ['clients', 'quotation', 'outlineAgreement', 'poHasGroupTasks.tasks', 'taskInfo', 'subcontractorTaskInfo', 'supplierPOData', 'subcontractorPOData']);

        if (empty($purchaseOrder)) {
            return $this->sendError('Purchase Order not found');
        }

        // $supplierIdList = array_unique($purchaseOrder->supplierProducts->pluck('supplier_id')->toArray());

        // $supplierList = collect([]);

        // foreach ($supplierIdList as $supplierId) {
        //     $supplierList->push(SupplierResource::make(Supplier::find($supplierId)));
        // }

        // $purchaseOrder->suppliers = $supplierList;

        return $this->sendResponse(PurchaseOrderResource::make($purchaseOrder), 'Purchase Order retrieved successfully');
    }

    /**
     * Update the specified PurchaseOrder in storage.
     * PUT/PATCH /purchaseOrders/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $this->authorize('purchase_order_update');

        $request->validate([
            'location_id' => 'required',
            'client_id'   => 'required',
        ]);

        $input = $request->all();

        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $this->purchaseOrderRepository->find($id);

        if (empty($purchaseOrder)) {
            return $this->sendError('Purchase Order not found');
        }

        // status control
        // have PO case
        // if (!$purchaseOrder->EMO) {
        if ($request->status >= 9) {
            if (!count($purchaseOrder->signedReceipts)) {
                return $this->sendError('Update status fail. Reason: Signed receipt could not be found.', 403);
            }
        }
        if ($request->status >= 7) {
            if (!count($purchaseOrder->signedInvoices)) {
                return $this->sendError('Update status fail. Reason: Signed invoice could not be found.', 403);
            }
        }
        if ($request->status >= 5) {
            foreach ($purchaseOrder->workOrders as $workOrder) {
                if (count($workOrder->resultImages) < 1) {
                    return $this->sendError('Update status fail. Reason: At least 1 result images must be taken for work instruction (id: ' . $workOrder->id . ').', 403);
                }
            }
        }
        if ($request->status >= 4 && $request->status != 5) {
            foreach ($purchaseOrder->workOrders as $workOrder) {
                if (!count($workOrder->completionSummaryPdfs)) {
                    return $this->sendError('Update status fail. Reason: Completion summary is not found (id: ' . $workOrder->id . ').', 403);
                }
            }
        }
        if ($request->status >= 3) {
            // foreach ($purchaseOrder->workOrders as $workOrder) {
            //     if ($workOrder->status != WorkOrder::COMPLETED) {
            //         return $this->sendError('Update status fail. Reason: Work Instruction (id: ' . $workOrder->id . ') is incomplete.', 403);
            //     }
            // }
            if (count($purchaseOrder->poHasGroupTasks)) {
                foreach ($purchaseOrder->poHasGroupTasks as $groupTask) {
                    if ($groupTask->status != GroupTask::TAKEN) {
                        return $this->sendError('Update status fail. Reason: Group Task (name: ' . $groupTask->group_task_name . '[id: ' . $groupTask->id . ']' . ') does not have a work instruction', 403);
                    }
                }
            } else {
                return $this->sendError('Update status fail. Reason: No group tasks are assigned to this PO.');
            }
        }
        if ($request->status > 2) {
            if (!count($purchaseOrder->workOrders)) {
                return $this->sendError('Update status fail. Reason: No work instructions are assigned.', 403);
            }
        }
        // }
        // else { // EMO case
        //     if ($request->status >= 1) {
        //         if (!count($purchaseOrder->workOrders)) {
        //             return $this->sendError('Update status fail. Reason: No work instructions are assigned.', 403);
        //         }
        //     }
        // }

        // DB::beginTransaction();
        // try {
        $purchaseOrder = $this->purchaseOrderRepository->update($input, $id);

        // handle clients
        if (count($purchaseOrder->clients)) {
            PurchaseOrderHasClient::where('purchase_order_id', $id)->first()->update([
                'purchase_order_id' => $id,
                'client_id'         => $input['client_id'],
                'status'            => PurchaseOrderHasClient::ACTIVE,
            ]);
        } else {
            PurchaseOrderHasClient::create(
                [
                    'purchase_order_id' => $id,
                    'client_id'         => $input['client_id'],
                    'status'            => PurchaseOrderHasClient::ACTIVE,
                ]
            );
        }

        $oriContact = $purchaseOrder->contactPeople->pluck('id')->toArray();
        $deleteContact = array_diff($oriContact, $input['contact_people_id']);
        $createContact = array_diff($input['contact_people_id'], $oriContact);

        foreach ($deleteContact as $contactId) {
            PurchaseOrderHasContactPerson::where('purchase_order_id', $id)->where('contact_person_id', $contactId)->forceDelete();
        }
        foreach ($createContact as $contactId) {
            PurchaseOrderHasContactPerson::create(
                [
                    'purchase_order_id' => $id,
                    'contact_person_id' => $contactId,
                    'status'            => PurchaseOrderHasContactPerson::ACTIVE,
                ]
            );
        }

        if (isset($request->subcontractor_task_info)) {
            foreach ($request->subcontractor_task_info as $groupTaskId => $taskInfoList) {
                foreach ($taskInfoList as $taskInfo) {
                    if (isset($taskInfo['task_id'])) {

                        $data = PurchaseOrderHasSubcontractorTask::updateOrCreate([
                            'purchase_order_id' => $taskInfo['purchase_order_id'],
                            'task_id'           => $taskInfo['task_id'],
                        ], [
                            'group_task_id'   => $taskInfo['group_task_id'] ?? null,
                            'group_task_name' => $taskInfo['group_task_name'] ?? null,
                            'task_name'       => $taskInfo['task_name'],
                            'qty'             => $taskInfo['qty'],
                            'task_no'         => $taskInfo['task_no'],
                            'unit'            => $taskInfo['unit'],
                            'unit_price'      => $taskInfo['unit_price'],
                            'status'          => $taskInfo['status'] ?? PurchaseOrderHasSubcontractorTask::ACTIVE,
                            'remark'          => $taskInfo['remark'] ?? null,
                            'total_price'     => $taskInfo['qty'] * $taskInfo['unit_price'],
                        ]);

                        if (isset($taskInfo['price_info'])) {
                            foreach ($taskInfo['price_info'] as $price) {
                                SubcontractorTaskPrice::updateOrCreate([
                                    'pivot_id'          => $data->id,
                                    'subcontractor_id'  => $price['subcontractor_id'],
                                    'purchase_order_id' => $price['purchase_order_id'],
                                    'task_id'           => $price['task_id'] ?? $taskInfo['task_id'],
                                ], [
                                    'subcontractor_name' => Subcontractor::find($price['subcontractor_id'])->name ?? null,
                                    'qty'                => $price['qty'] ?? 0,
                                    'actual_qty'         => $price['actual_qty'] ?? 0,
                                    'payment_qty'        => $price['payment_qty'] ?? 0,
                                    'unit_price'         => $price['unit_price'] ?? null,
                                ]);
                            }
                        }

                    } else {
                        // create from supplier form
                        $task = Task::create([
                            'name'        => $taskInfo['task_name'],
                            'qty'         => $taskInfo['qty'] ?? null,
                            'task_no'     => $taskInfo['task_no'] ?? null,
                            'unit'        => $taskInfo['unit'],
                            'unit_price'  => $taskInfo['unit_price'] ?? null,
                            'total_price' => ($taskInfo['qty'] ?? 0) * ($taskInfo['unit_price'] ?? 0),
                        ]);

                        $pivot = PurchaseOrderHasSubcontractorTask::create([
                            'purchase_order_id' => $id,
                            'task_name'         => $task->name,
                            'task_id'           => $task->id,
                            'task_no'           => $task->task_no,
                            'status'            => PurchaseOrderHasSubcontractorTask::ACTIVE,
                            'remark'            => $taskInfo['remark'] ?? null,
                            'qty'               => $task->qty,
                            'unit'              => $task->unit,
                            'unit_price'        => $task->unit_price,
                            'total_price'       => $task->total_price,
                        ]);

                        if (isset($taskInfo['price_info'])) {
                            foreach ($taskInfo['price_info'] as $price) {

                                SubcontractorTaskPrice::updateOrCreate([
                                    'pivot_id'          => $pivot->id,
                                    'subcontractor_id'  => $price['subcontractor_id'],
                                    'purchase_order_id' => $id,
                                    'task_id'           => $price['task_id'] ?? $task->id,
                                ], [
                                    'subcontractor_name' => Subcontractor::find($price['subcontractor_id'])->name ?? null,
                                    'qty'                => $price['qty'] ?? 0,
                                    'actual_qty'         => $price['actual_qty'] ?? 0,
                                    'payment_qty'        => $price['payment_qty'] ?? 0,
                                    'unit_price'         => $price['unit_price'] ?? null,
                                ]);
                            }
                        }

                    }
                }

            }

            // handle POHasSupplier
            // if ($request->suppliers_id) {
            $oriSupplier = $purchaseOrder->suppliers->pluck('id')->toArray();

            // create task price info with null qty/price
            $createIDList = array_diff($input['suppliers_id'], $oriSupplier);
            foreach ($createIDList as $createId) {
                PurchaseOrderHasSupplier::create([
                    'purchase_order_id' => $id,
                    'supplier_id'       => $createId,
                ]);

            }

            // delete task price info also
            $deleteIDList = array_diff($oriSupplier, $input['suppliers_id']);
            foreach ($deleteIDList as $deleteId) {
                PurchaseOrderHasSupplier::where('supplier_id', $deleteId)->where('purchase_order_id', $id)->forceDelete();
            }

            // }
            // handle POHasSubcontractor
            // if ($request->subcontractors_id) {
            $oriSubcontractor = $purchaseOrder->subcontractors->pluck('id')->toArray();
            // M2M::updateM2M($id, $oriSubcontractor, $input['subcontractors_id'], 'App\Models\PurchaseOrderHasSubcontractor');

            // create task price info with null qty/price
            $createIDList = array_diff($input['subcontractors_id'], $oriSubcontractor);
            foreach ($createIDList as $createId) {
                PurchaseOrderHasSubcontractor::create([
                    'purchase_order_id' => $id,
                    'subcontractor_id'  => $createId,
                ]);
                foreach ($purchaseOrder->subcontractorTaskInfo as $task) {

                    SubcontractorTaskPrice::firstOrCreate([
                        'pivot_id'          => $task->id,
                        'subcontractor_id'  => $createId,
                        'purchase_order_id' => $id,
                        'task_id'           => $task->task_id,
                    ], [
                        'subcontractor_name' => Subcontractor::find($createId)->name ?? 'subcontractor table bug',
                        'qty'                => 0,
                        'actual_qty'         => 0,
                        'payment_qty'        => 0,
                        'unit_price'         => SubcontractorHasTask::where('task_id', $task->task_id)->where('subcontractor_id', $createId)->first()->unit_price ?? null,
                    ]);
                }

            }

            // delete task price info also
            $deleteIDList = array_diff($oriSubcontractor, $input['subcontractors_id']);
            foreach ($deleteIDList as $deleteId) {
                PurchaseOrderHasSubcontractor::where('subcontractor_id', $deleteId)->where('purchase_order_id', $id)->forceDelete();
                SubcontractorTaskPrice::where('subcontractor_id', $deleteId)->where('purchase_order_id', $id)->forceDelete();
            }
            // }

            // handle group tasks
            if ($request->is_contract) {
                if (isset($input['group_task_id'])) {
                    $oriGroupTask = $purchaseOrder->poHasGroupTasks->pluck('id')->toArray();

                    $deleteGroupTask = array_diff($oriGroupTask, $input['group_task_id']);
                    $createGroupTask = array_diff($input['group_task_id'], $oriGroupTask);

                    foreach ($deleteGroupTask as $groupTaskId) {
                        PurchaseOrderHasGroupTask::where('purchase_order_id', $id)->where('group_task_id', $groupTaskId)->forceDelete();
                        // if work order related to this PO has this group task, delete the pivot row
                        foreach ($purchaseOrder->workOrders as $workOrder) {
                            WorkOrderHasGroupTask::where('work_order_id', $workOrder->id)
                                ->where('group_task_id', $groupTaskId)
                                ->forceDelete();
                        }
                        // delete PO task price data (no soft delete model)
                        PurchaseOrderHasTask::where('purchase_order_id', $id)->where('group_task_id', $groupTaskId)->delete();
                        $pivot = PurchaseOrderHasSubcontractorTask::where('purchase_order_id', $id)->where('group_task_id', $groupTaskId)->first();
                        foreach ($pivot->priceInfo as $price) {
                            $price->forceDelete();
                        }
                        $pivot->forceDelete();
                    }
                    foreach ($createGroupTask as $groupTaskId) {
                        PurchaseOrderHasGroupTask::create(
                            [
                                'purchase_order_id' => $id,
                                'group_task_id'     => $groupTaskId,
                                'status'            => PurchaseOrderHasGroupTask::ACTIVE,
                                'actual_qty'        => GroupTask::find($groupTaskId)->qty,
                                'actual_price'      => GroupTask::find($groupTaskId)->total_price,
                            ]
                        );
                        // create PO task price data
                        $groupTask = GroupTask::find($groupTaskId);
                        $tasks = $groupTask->tasks;
                        if (count($tasks)) {
                            foreach ($tasks as $task) {
                                // initialize qty, unit price as stored in Task model
                                PurchaseOrderHasTask::create([
                                    'purchase_order_id' => $id,
                                    'group_task_id'     => $groupTaskId,
                                    'group_task_name'   => $groupTask->display_name,
                                    'task_name'         => $task->name,
                                    'task_id'           => $task->id,
                                    'task_no'           => $task->task_no,
                                    'status'            => PurchaseOrderHasTask::ACTIVE,
                                    'qty'               => $task->qty,
                                    'unit'              => $task->unit,
                                    'unit_price'        => $task->unit_price,
                                    'total_price'       => $task->total_price,
                                ]);
                                $pivot = PurchaseOrderHasSubcontractorTask::create([
                                    'purchase_order_id' => $id,
                                    'group_task_id'     => $groupTaskId,
                                    'group_task_name'   => $groupTask->display_name,
                                    'task_name'         => $task->name,
                                    'task_id'           => $task->id,
                                    'task_no'           => $task->task_no,
                                    'status'            => PurchaseOrderHasTask::ACTIVE,
                                    'qty'               => $task->qty,
                                    'unit'              => $task->unit,
                                    'unit_price'        => $task->unit_price,
                                    'total_price'       => $task->total_price,
                                ]);

                                foreach ($purchaseOrder->subcontractors as $subcontractor) {

                                    // create blank price data
                                    SubcontractorTaskPrice::create([
                                        'pivot_id'           => $pivot->id,
                                        'subcontractor_id'   => $subcontractor->id,
                                        'purchase_order_id'  => $id,
                                        'task_id'            => $task->id,
                                        'subcontractor_name' => $subcontractor->name,
                                        'qty'                => 0,
                                        'actual_qty'         => 0,
                                        'payment_qty'        => 0,
                                        'unit_price'         => null,
                                    ]);
                                }
                            }
                        }
                    }
                }
            } else if (isset($request->group_tasks)) { // this is not contract mode
                $tmp = [];
                // Map outline agreement with group tasks and tasks
                foreach ($input['group_tasks'] as $inputGroupT) {
                    $tmp[$inputGroupT['name']] = array_merge(['group_task_id' => $inputGroupT['id']], ['tasks' => $inputGroupT['tasks']]);

                }

                MapTaskNew::mapTasks('purchase_order_id', $purchaseOrder->id, $tmp, 'App\Models\PurchaseOrderHasGroupTask');

            }
            if ($request->task_info) {

                // TODO: find out the tasks to be deleted
                // $oriTask = $purchaseOrder->taskInfo->pluck('id')->toArray();

                // foreach ($request->task_info as $groupTaskId => $groupTask) {
                //     $inputTask = collect($input['task_info'][$groupTaskId])->reject(function ($task) {
                //         return (!isset($task['task_id']));})->pluck('id')->toArray();

                //     $deleteTaskInfoIds = array_diff($oriTask, $inputTask);

                //     Log::debug($deleteTaskInfoIds);

                //     foreach ($deleteTaskInfoIds as $deleteId) {
                //         $deleteTask = PurchaseOrderHasTask::find($deleteId);
                //         PurchaseOrderHasSubcontractorTask::where('purchase_order_id', $id)
                //             ->where('task_id', $deleteTask->task_id)->forceDelete();
                //         $deleteTask->forceDelete();
                //     }
                // }

                // tasks to be updated/created
                foreach ($request->task_info as $groupTaskId => $groupTask) {
                    foreach ($groupTask as $taskInfo) {
                        if (!isset($taskInfo['task_id'])) {
                            // newly added task
                            $groupTask = GroupTask::find($groupTaskId);
                            $task = Task::create([
                                'group_task_id' => $groupTaskId,
                                'name'          => $taskInfo['task_name'],
                                'qty'           => $taskInfo['qty'],
                                'task_no'       => $taskInfo['task_no'],
                                'unit'          => $taskInfo['unit'],
                                'unit_price'    => $taskInfo['unit_price'],
                                'total_price'   => $taskInfo['qty'] * $taskInfo['unit_price'],
                            ]);
                            PurchaseOrderHasTask::create([
                                'purchase_order_id' => $id,
                                'group_task_id'     => $groupTaskId,
                                'group_task_name'   => $groupTask->display_name,
                                'task_name'         => $task->name,
                                'task_id'           => $task->id,
                                'task_no'           => $task->task_no,
                                'status'            => PurchaseOrderHasTask::ACTIVE,
                                'qty'               => $task->qty,
                                'unit'              => $task->unit,
                                'unit_price'        => $task->unit_price,
                                'total_price'       => $task->total_price,
                            ]);
                            PurchaseOrderHasSubcontractorTask::create([
                                'purchase_order_id' => $id,
                                'group_task_id'     => $groupTaskId,
                                'group_task_name'   => $groupTask->display_name,
                                'task_name'         => $task->name,
                                'task_id'           => $task->id,
                                'task_no'           => $task->task_no,
                                'status'            => PurchaseOrderHasTask::ACTIVE,
                                'qty'               => $task->qty,
                                'unit'              => $task->unit,
                                'unit_price'        => $task->unit_price,
                                'total_price'       => $task->total_price,
                            ]);

                        } else {
                            // update existing task
                            PurchaseOrderHasTask::where('purchase_order_id', $taskInfo['purchase_order_id'])
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
                            PurchaseOrderHasSubcontractorTask::where('purchase_order_id', $taskInfo['purchase_order_id'])
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

                //     DB::commit();
                // } catch (\Exception$e) {
                //     DB::rollBack();
                //     return $this->sendApiError($e->getMessage());
                // }
                $purchaseOrder->update([
                    'total_price' => $purchaseOrder->taskInfo()->sum('total_price'),
                ]);
                $purchaseOrder->refresh();
                return $this->sendResponse(PurchaseOrderResource::make($purchaseOrder), 'PurchaseOrder updated successfully');
            }
        }
    }

    /**
     * Remove the specified PurchaseOrder from storage.
     * DELETE /purchaseOrders/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        $this->authorize('purchase_order_delete');

        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $this->purchaseOrderRepository->find($id);

        if (empty($purchaseOrder)) {
            return $this->sendError('Purchase Order not found');
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::DELETED,
        ]);

        return $this->sendSuccess('Purchase Order deleted successfully');
    }

    public function genQuotation($id, Request $request)
    {
        // get modal data
        $contactId = $request->modalPersonId;
        $groupTasks = $request->groupTasks;
        $quotRef = $request->quot_ref;
        $letterhead = $request->letterhead;

        // update group task position
        foreach ($groupTasks as $groupTask) {
            $pivot = PurchaseOrderHasGroupTask::where('purchase_order_id', $id)->where('group_task_id', $groupTask['id'])->first();
            $pivot->update([
                'position' => $groupTask['position'],
            ]);
        }

        $purchaseOrder = PurchaseOrder::find($id);
        $contactPerson = ContactPerson::find($contactId);
        $groupTaskArray = $purchaseOrder->taskInfo->groupBy('group_task_id');

        if (!count($groupTaskArray)) {
            return $this->sendError('No group tasks are linked yet. Please update PO.');
        }

        $pattern = "/r\d+$/";
        preg_match($pattern, $quotRef, $matches);
        $extractedRef = str_replace($matches[0] ?? '', '', $quotRef);

        // Check if the quot_ref is repeated
        $repeatRefPO = PurchaseOrder::where('quot_ref', 'LIKE', '%' . $extractedRef . '%')->where('id', '!=', $id)->first();
        if (isset($repeatRefPO)) {
            $suffix = 'A'; // Start with 'A' as the initial suffix

            // Loop until a unique quot_ref is found
            while (PurchaseOrder::where('quot_ref', 'LIKE', '%' . $extractedRef . $suffix . '%')->exists()) {
                // Increment the suffix
                $suffix++;
            }

            // Append the suffix to the quot_ref
            $quotRef = $extractedRef . $suffix;
        }

        // Check if the existing quotation has repeated filename:
        // same location code, same date, but different PO needs different ref no
        $repeatRefPO = Asset::where('file_name', $quotRef)->exists();
        if ($repeatRefPO && $request->status == PurchaseOrder::FORMAL) {
            $suffix = 1; // Start with '1' as the initial suffix

            // Extract the number from the end of the filename
            preg_match('/r(\d+)$/', $quotRef, $matches);
            if (!empty($matches)) {
                $suffix = intval($matches[1]) + 1;
                $quotRef = preg_replace('/r(\d+)$/', '', $quotRef); // Remove the old suffix
            }

            // Loop until a unique quot_ref is found
            while (Asset::where('file_name', $quotRef . 'r' . $suffix)->exists()) {
                // Increment the suffix
                $suffix++;
            }

            // Append the suffix to the quot_ref
            $quotRef .= 'r' . $suffix;
        }

        // update latest quot ref if customized
        $purchaseOrder->update([
            'quot_letterhead' => $request->letterhead,
            'quot_ref'        => $quotRef,
            'quot_date'       => Carbon::parse($request->quot_date),
        ]);

        $purchaseOrder->refresh();

        $quotRefTime = $quotRef . '-' . time();

        // handle empty cases on backend data
        if (empty($purchaseOrder)) {
            return $this->sendError('Purchase order not found.');
        }

        if (empty($contactPerson)) {
            return $this->sendError('Contact person not found.');
        }

        if (!count($purchaseOrder->poHasGroupTasks)) {
            return $this->sendError('No group tasks assigned to this PO.');
        }

        // generate pdf using laravel-dompdf
        $files = [];

        foreach ($purchaseOrder->clients as $client) {
            if (!count($purchaseOrder->contactPeople)) {
                return $this->sendError('Quotation genenation fail. Reason: No contact people are selected.');
            } else {
                $groupTasks = $purchaseOrder->poHasGroupTasks;

                // quotation date back if specified
                if ($purchaseOrder->quot_date) {
                    $date = Carbon::parse($purchaseOrder->quot_date)->format('d-M-Y');
                } else {
                    $date = Carbon::parse(now())->format('d-M-Y');
                }

                // make directory if not available {
                if (!is_dir(public_path('quotation'))) {
                    mkdir(public_path('quotation'));
                }
                if (!is_dir(public_path('quotation-draft'))) {
                    mkdir(public_path('quotation-draft'));
                }

                if ($purchaseOrder->is_contract) {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('report.quotation-test', ['data' => compact('purchaseOrder', 'quotRef', 'date', 'client', 'contactPerson', 'groupTasks', 'groupTaskArray', 'letterhead')]);

                    if ($letterhead == PurchaseOrder::COMPANY_A) {

                        $pdf->output();

                        // add watermark to pdf
                        $canvas = $pdf->getDomPDF()->getCanvas();

                        $height = $canvas->get_height();
                        $width = $canvas->get_width();

                        $canvas->page_script('
                        $pdf->set_opacity(0.3);
                        $pdf->set_opacity(0.3, "Multiply");
                        $pdf->image(public_path("W1_letter_watermark.png"), ' . $width . ' / 3 + 1, ' . $height . ' / 5 * 2, 523 * 0.72, 516 * 0.72);
                        ');
                    }
                    if ($request->status == PurchaseOrder::DRAFT) {
                        $folderName = 'quotation-draft';
                    } else {
                        $folderName = 'quotation';
                    }

                    // Define the path to the PDF file
                    if ($request->status == PurchaseOrder::DRAFT) {
                        $filePath = public_path($folderName) . '/' . str_replace('/', '-', $quotRefTime) . '.pdf';
                    } else {
                        $filePath = public_path($folderName) . '/' . str_replace('/', '-', $quotRefTime) . '.pdf';
                    }

                    // Save the new PDF file
                    $pdf->save($filePath);

                } else {
                    return $this->sendError('No contract type to be done.');
                    // $pdf = PDF::loadView('report.quotation-no-contract', compact('purchaseOrder', 'ref', 'date', 'client', 'contactPerson', 'groupTask'));
                    // \File::put(public_path('quotation') . '/' . $ref . '.pdf', $pdf->Output($ref . '.pdf', "S"));
                }

                $path = asset($folderName . '/' . str_replace('/', '-', $quotRefTime) . '.pdf');

                array_push($files, $path);

                if ($request->status == PurchaseOrder::FORMAL) {
                    // add revise count
                    $purchaseOrder->update([
                        'revise_count' => $purchaseOrder->revise_count + 1,
                    ]);

                    // create record in backend
                    Asset::create([
                        'related_type'  => $this->purchaseOrderRepository->model(),
                        'related_id'    => $purchaseOrder->id,
                        'asset_type'    => PurchaseOrder::QUOTATION,
                        'url'           => '/quotation/' . $quotRefTime . '.pdf',
                        'resource_path' => public_path('quotation') . '/' . str_replace('/', '-', $quotRefTime) . '.pdf',
                        'file_size'     => 0,
                        'file_name'     => $quotRef,
                        'status'        => Asset::ACTIVE,
                    ]);
                }

            }
        }

        return $this->sendResponse(['file_path' => $files], 'Quotation successfully generated.');

    }

    public function genSupplierPO($id, Request $request)
    {

        $files = [];

        $purchaseOrder = PurchaseOrder::find($id);

        if (empty($purchaseOrder)) {
            return $this->sendError('Purchase order not found.');
        }

        foreach ($purchaseOrder->clients as $client) {

            // $ref = $purchaseOrder->client->code . '-PO-' . Carbon::parse(now())->format('ymd');
            if (!count($purchaseOrder->contactPeople)) {
                return $this->sendError('Supplier PO genenation fail. Reason: No contact people are selected.');
            } else {

                $products = $purchaseOrder->products()->where('supplier_id', $request->supplierId)->get();

                if ($products->isEmpty()) {
                    return $this->sendError('Supplier PO genenation fail. Reason: No supplier products are added. Please update and try again.');
                }

                $fileName = Str::uuid();
                $supplier = Supplier::find($request->supplierId);
                $paymentTerm = $request->payment_term;
                $deliveryMode = $request->delivery_mode; // = shipping address
                // keep record of supplier po tasks
                $supplierPOData = SupplierPOData::updateOrCreate([
                    'purchase_order_id' => $id,
                    'supplier_id'       => $request->supplierId,
                    'letterhead'        => $request->letterhead ?? SupplierPOData::COMPANY_A,
                ], [
                    'delivered_at'           => $request->expect_delivered_at,
                    'issued_at'              => $request->issued_at,
                    'remark'                 => $request->remark,
                    'shipping_address'       => $request->delivery_mode,
                    'payment_term'           => $request->payment_term,
                    'exp_working_started_at' => $request->exp_working_started_at,
                    'exp_working_ended_at'   => $request->exp_working_ended_at,
                ]);

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('report.supplier-po', ['data' => compact('products', 'supplier', 'purchaseOrder', 'paymentTerm', 'deliveryMode', 'supplierPOData')]);

                // foreach ($products as $product) {
                //     SupplierPOHasTask::updateOrCreate([
                //         'purchase_order_id' => $id,
                //         'supplier_po_id'    => $supplierPOData->id,
                //         'group_task_id'     => $product->group_task_id,
                //     ], [
                //         'group_task_name' => $product->group_task_name,
                //         'task_name'       => $product->task_name,
                //         'task_id'         => $product->task_id,
                //         'task_no'         => $product->task_no,
                //         'qty'             => $product->qty,
                //         'unit'            => $product->unit,
                //         'unit_price'      => $product->unit_price,
                //         'total_price'     => $product->total_price,
                //         'status'          => $product->status,
                //         'remark'          => $product->remark,
                //     ]);
                // }

                // save pdf into folder
                if (!is_dir(public_path('supplier-po'))) {
                    mkdir(public_path('supplier-po'));
                }
                $storePath = public_path('supplier-po') . '/' . $fileName . '.pdf';

                $pdf->save($storePath);

                $path = asset('supplier-po/' . $fileName . '.pdf');

                array_push($files, $path);

            }
        }

        return $this->sendResponse(['file_path' => $files], 'Supplier PO successfully generated.');
    }

    public function genSubcontractorPO($id, Request $request)
    {

        $files = [];

        $purchaseOrder = PurchaseOrder::find($id);

        if (empty($purchaseOrder)) {
            return $this->sendError('Purchase order not found.');
        }

        foreach ($purchaseOrder->clients as $client) {

            if (!count($purchaseOrder->contactPeople)) {
                return $this->sendError('Subcontractor PO genenation fail. Reason: No contact people are selected.');
            } else {

                $products = $purchaseOrder->subcontractorTaskPrice()->where('subcontractor_task_prices.subcontractor_id', $request->subcontractor_id)->get();

                if ($products->isEmpty()) {
                    return $this->sendError('Subcontractor PO genenation fail. Reason: No subcontractor tasks are added. Please update and try again.');
                }

                $fileName = Str::uuid();
                $subcontractor = Subcontractor::find($request->subcontractor_id);
                $paymentTerm = $request->payment_term;
                $deliveryMode = $request->delivery_mode; // = shipping address
                // keep record of subcontractor po tasks
                $subcontractorPOData = SubcontractorPOData::updateOrCreate([
                    'purchase_order_id' => $id,
                    'subcontractor_id'  => $request->subcontractor_id,
                    'letterhead'        => $request->letterhead ?? SubcontractorPOData::COMPANY_A,
                ], [
                    'delivered_at'           => $request->expect_delivered_at,
                    'issued_at'              => $request->issued_at,
                    'remark'                 => $request->remark,
                    'shipping_address'       => $request->delivery_mode,
                    'payment_term'           => $request->payment_term,
                    'exp_working_started_at' => $request->exp_working_started_at,
                    'exp_working_ended_at'   => $request->exp_working_ended_at,
                ]);

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('report.subcontractor-po', ['data' => compact('products', 'subcontractor', 'purchaseOrder', 'paymentTerm', 'deliveryMode', 'subcontractorPOData')]);

                // foreach ($products as $product) {
                //     SubcontractorPOHasTask::updateOrCreate([
                //         'purchase_order_id' => $id,
                //         'subcontractor_po_id'    => $subcontractorPOData->id,
                //         'group_task_id'     => $product->group_task_id,
                //     ], [
                //         'group_task_name' => $product->group_task_name,
                //         'task_name'       => $product->task_name,
                //         'task_id'         => $product->task_id,
                //         'task_no'         => $product->task_no,
                //         'qty'             => $product->qty,
                //         'unit'            => $product->unit,
                //         'unit_price'      => $product->unit_price,
                //         'total_price'     => $product->total_price,
                //         'status'          => $product->status,
                //         'remark'          => $product->remark,
                //     ]);
                // }

                // save pdf into folder
                if (!is_dir(public_path('subcontractor-po'))) {
                    mkdir(public_path('subcontractor-po'));
                }
                $storePath = public_path('subcontractor-po') . '/' . $fileName . '.pdf';

                $pdf->save($storePath);

                $path = asset('subcontractor-po/' . $fileName . '.pdf');

                array_push($files, $path);

            }
        }

        return $this->sendResponse(['file_path' => $files], 'Subcontractor PO successfully generated.');
    }

    public function genInvoice($id)
    {
        $files = [];

        $purchaseOrder = PurchaseOrder::find($id);

        foreach ($purchaseOrder->contactPeople as $contactPerson) {
            $fileName = Str::uuid();

            // calculating total for whole invoice
            $sumTotal = 0;

            foreach ($purchaseOrder->workOrders as $workOrder) {
                $sumTotal += $workOrder->groupTasks->sum('total_price');
            }

            $sumTotal = number_format((float) $sumTotal, 2, ',', '');

            $pdf = PDF::loadView('report.invoice', compact('purchaseOrder', 'contactPerson', 'sumTotal'));

            if (!is_dir(public_path('invoice'))) {
                mkdir(public_path('invoice'));
            }
            \File::put(public_path('invoice') . '/' . $fileName . '.pdf', $pdf->Output($fileName . '.pdf', "S"));

            $path = asset('invoice/' . $fileName . '.pdf');

            array_push($files, $path);
        }

        return $this->sendResponse(['file_path' => $files], 'Invoice successfully generated.');
    }
    public function genReceipt($id)
    {
        $files = [];

        $purchaseOrder = PurchaseOrder::find($id);

        foreach ($purchaseOrder->contactPeople as $contactPerson) {
            $fileName = Str::uuid();

            // calculating total for whole receipt
            $sumTotal = 0;

            foreach ($purchaseOrder->workOrders as $workOrder) {
                $sumTotal += $workOrder->groupTasks->sum('total_price');
            }

            $sumTotal = number_format((float) $sumTotal, 2, ',', '');

            $pdf = PDF::loadView('report.receipt', compact('purchaseOrder', 'contactPerson', 'sumTotal'));

            if (!is_dir(public_path('receipt'))) {
                mkdir(public_path('receipt'));
            }
            \File::put(public_path('receipt') . '/' . $fileName . '.pdf', $pdf->Output($fileName . '.pdf', "S"));

            $path = asset('receipt/' . $fileName . '.pdf');

            array_push($files, $path);
        }

        return $this->sendResponse(['file_path' => $files], 'Invoice successfully generated.');

    }

    public function numberToEnglish($number)
    {
        $ones = [
            0  => 'zero', 1 => 'one', 2     => 'two', 3     => 'three', 4     => 'four', 5      => 'five', 6     => 'six', 7      => 'seven', 8      => 'eight', 9     => 'nine',
            10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen',
        ];
        $tens = [2 => 'twenty', 3 => 'thirty', 4 => 'forty', 5 => 'fifty', 6 => 'sixty', 7 => 'seventy', 8 => 'eighty', 9 => 'ninety'];
        $others = [100 => 'hundred', 1000 => 'thousand', 1000000 => 'million', 1000000000 => 'billion', 1000000000000 => 'trillion'];

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            return 'minus ' . $this->numberToEnglish(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 20:
                $string = $ones[$number];
                break;
            case $number < 100:
                $string = $tens[floor($number / 10)];
                $string .= ($number % 10) ? ' ' . $ones[$number % 10] : '';
                break;
            case $number < 1000:
                $string = $ones[floor($number / 100)] . ' ' . $others[100];
                $string .= ($number % 100) ? ' ' . $this->numberToEnglish($number % 100) : '';
                break;
            default:
                foreach ($others as $key => $value) {
                    if ($number < $key) {
                        $amount = floor($number / ($key / 1000));
                        $string .= $this->numberToEnglish($amount) . ' ' . $value;
                        $number -= $amount * ($key / 1000);
                    }
                }
                break;
        }

        if (null !== $fraction) {
            $string .= ' point';
            foreach (str_split((string) $fraction) as $number) {
                $string .= ' ' . $ones[$number];
            }
        }

        return $string;
    }

    public static function syncPOPivot($po)
    {
        if (isset($po->poHasGroupTasks)) {
            foreach ($po->poHasGroupTasks as $groupTask) {
                if (isset($groupTask->tasks)) {
                    foreach ($groupTask->tasks as $task) {
                        PurchaseOrderHasTask::firstOrCreate([
                            'purchase_order_id' => $po->id,
                            'group_task_id'     => $groupTask->id,
                            'task_id'           => $task->id,
                        ], [
                            'group_task_name' => $groupTask->group_task_name,
                            'task_name'       => $task->name,
                            'task_no'         => $task->task_no,
                            'qty'             => $task->qty,
                            'unit'            => $task->unit,
                            'unit_price'      => $task->unit_price,
                            'total_price'     => $task->total_price,
                        ]);
                        PurchaseOrderHasSubcontractorTask::firstOrCreate([
                            'purchase_order_id' => $po->id,
                            'group_task_id'     => $groupTask->id,
                            'task_id'           => $task->id,
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
        // sync total_price to PO
        $po->update([
            'total_price' => $po->taskInfo()->sum('total_price'),
        ]);

    }

    public function updatePOProducts($id, Request $request)
    {
        $this->authorize('purchase_order_update');

        $input = $request->all();

        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $this->purchaseOrderRepository->find($id);

        if (empty($purchaseOrder)) {
            return $this->sendError('Purchase Order not found');
        }

        $inputIDList = collect([]);
        if (isset($request->products)) {
            foreach ($request->products as $supplierId => $productList) {
                if (isset($productList)) {

                    $inputIDList = $inputIDList->merge(collect($productList)->pluck('product_id'));
                    // return response()->json(collect($productList)->pluck('product_id'));

                    foreach ($productList as $product) {

                        // update/create price history
                        ProductPriceHistory::updateOrCreate([
                            'purchase_order_id' => $id,
                            'product_id'        => $product['product_id'],
                        ], [
                            'name'        => $product['product_name'],
                            'desc'        => $product['desc'],
                            'unit'        => $product['unit'],
                            'unit_price'  => $product['unit_price'],
                            'qty'         => $product['qty'],
                            'total_price' => $product['unit_price'] * $product['qty'],
                            'product_no'  => $product['product_no'],
                            'status'      => $product['status'] ?? ProductPriceHistory::ACTIVE,
                        ]);
                    }

                }
            }
        }

        // delete price history
        $oriIDList = $purchaseOrder->products->pluck('id')->toArray();
        $deleteIDList = array_diff($oriIDList, $inputIDList->toArray());
        foreach ($deleteIDList as $deleteProductId) {
            ProductPriceHistory::where('purchase_order_id', $id)->where('product_id', $deleteProductId)->forceDelete();
        }

        return $this->sendResponse(PurchaseOrderResource::make($purchaseOrder), 'PurchaseOrder updated successfully');
    }

    public function fillSubconTasks($poId, Request $request)
    {
        $this->authorize('purchase_order_update');

        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $this->purchaseOrderRepository->find($poId);

        if (empty($purchaseOrder)) {
            return $this->sendError('Purchase Order not found');
        }

        $pivot = PurchaseOrderHasSubcontractorTask::updateOrCreate([
            'purchase_order_id' => $poId,
            'task_id'           => $request->task_id,
        ], [
            'group_task_id'    => $request->group_task_id,
            'subcontractor_id' => $request->subcontractor_id,
            'group_task_name'  => $request->group_task_name,
            'task_name'        => $request->task_name,
            'task_no'          => $request->task_no,
            'qty'              => $request->qty,
            'unit'             => $request->unit,
            'unit_price'       => $request->unit_price,
            'total_price'      => $request->qty * $request->unit_price,
        ]);

        foreach ($purchaseOrder->subcontractors as $subcontractor) {
            // create blank data in task price
            SubcontractorTaskPrice::updateOrCreate([
                'purchase_order_id' => $poId,
                'task_id'           => $request->task_id,
                'subcontractor_id'  => $subcontractor->id,
            ], [
                'pivot_id'           => $pivot->id,
                'subcontractor_name' => $subcontractor->name,
                'qty'                => 0,
                'actual_qty'         => 0,
                'payment_qty'        => 0,
                'unit_price'         => null,
            ]);
        }

        return $this->sendResponse($pivot->toArray(), 'Subcon tasks filled successfully');

    }

    public static function syncPivot()
    {
        $purchaseOrders = PurchaseOrder::get();
        foreach ($purchaseOrders as $po) {
            self::syncPOPivot($po);
        }
    }

}
