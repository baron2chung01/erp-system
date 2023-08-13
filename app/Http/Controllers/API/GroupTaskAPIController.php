<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateGroupTaskAPIRequest;
use App\Http\Requests\API\UpdateGroupTaskAPIRequest;
use App\Http\Resources\GroupTaskResource;
use App\Models\GroupTask;
use App\Models\OutlineAgreementHasGroupTask;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderHasGroupTask;
use App\Models\PurchaseOrderHasTask;
use App\Models\WorkOrderHasGroupTask;
use App\Models\WorkOrderHasTask;
use App\Repositories\GroupTaskRepository;
use App\Repositories\TaskRepository;
use App\Traits\Arr;
use Illuminate\Http\Request;
use Response;

/**
 * Class GroupTaskController
 * @package App\Http\Controllers\API
 */
class GroupTaskAPIController extends AppBaseController
{
    /** @var  GroupTaskRepository */
    private $groupTaskRepository, $taskRepository;

    public function __construct(GroupTaskRepository $groupTaskRepo, TaskRepository $taskRepo)
    {
        $this->groupTaskRepository = $groupTaskRepo;
        $this->taskRepository = $taskRepo;
    }

    /**
     * Display a listing of the GroupTask.
     * GET|HEAD /groupTasks
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        list($input, $current, $pageSize) = $this->getInput($request);

        $checkEmpty = $request->all();
        if (empty($checkEmpty)) {
            return response()->json(GroupTask::get()->mapWithKeys(function ($item) {
                return [$item->id => $item->group_task_name];
            })
            );
        }

        $groupTasks = $this->groupTaskRepository->all(
            $input,
            $current,
            $pageSize
        );

        $total = count($this->groupTaskRepository->all(
            $input
        ));

        return $this->sendResponse([
            'data'  => GroupTaskResource::collection($groupTasks),
            'total' => $total,
        ], 'Group Tasks retrieved successfully');
    }

    /**
     * Store a newly created GroupTask in storage.
     * POST /groupTasks
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(CreateGroupTaskAPIRequest $request)
    {
        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        // validate tasks
        foreach ($input['tasks'] as $task) {
            if (!array_key_exists('name', $task) || !array_key_exists('qty', $task) || !array_key_exists('unit', $task) || !array_key_exists('unit_price', $task)) {
                return $this->sendError('Tasks fields are uncompleted.');
            }
        }

        $groupTask = GroupTask::create([
            'group_task_name' => $input['group_task_name'],
        ]);

        foreach ($input['tasks'] as $task) {
            $task['group_task_id'] = $groupTask->id;
            $task['total_price'] = $task['unit_price'] * $task['qty'];
            $this->taskRepository->create($task);
        }

        $groupTask->update([
            'total_price' => $groupTask->tasks->sum('total_price'),
        ]);

        // connect to OA if outline agreement id exists
        if (isset($input['outline_agreement_id'])) {
            OutlineAgreementHasGroupTask::firstOrCreate([
                'outline_agreement_id' => $input['outline_agreement_id'],
                'group_task_id'        => $groupTask->id,
            ],
                [
                    'status'       => OutlineAgreementHasGroupTask::ACTIVE,
                    'actual_price' => $groupTask->total_price,
                    'actual_qty'   => 1,
                ]);
        }

        // connect to PO if purchase order id exists
        if (isset($input['purchase_order_id'])) {
            PurchaseOrderHasGroupTask::firstOrCreate([
                'purchase_order_id' => $input['purchase_order_id'],
                'group_task_id'     => $groupTask->id,
            ],
                [
                    'status'       => PurchaseOrderHasGroupTask::ACTIVE,
                    'actual_price' => $groupTask->total_price,
                    'actual_qty'   => 1,
                ]);

            // sync to supplier level
            PurchaseOrderAPIController::syncPOPivot(PurchaseOrder::find($input['purchase_order_id']));
        }

        $groupTask->refresh();

        return $this->sendResponse(GroupTaskResource::make($groupTask), 'Group Task saved successfully');
    }

    /**
     * Display the specified GroupTask.
     * GET|HEAD /groupTasks/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var GroupTask $groupTask */
        $groupTask = $this->groupTaskRepository->find($id, ['tasks']);

        if (empty($groupTask)) {
            return $this->sendError('Group Task not found');
        }

        return $this->sendResponse(GroupTaskResource::make($groupTask), 'Group Task retrieved successfully');
    }

    /**
     * Update the specified GroupTask in storage.
     * PUT/PATCH /groupTasks/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        /** @var GroupTask $groupTask */
        $groupTask = $this->groupTaskRepository->find($id);

        if (empty($groupTask)) {
            return $this->sendError('Group Task not found');
        }

        $groupTask = $this->groupTaskRepository->update($input, $id);

        foreach ($input['tasks'] as $task) {
            if (isset($task['id'])) {
                $this->taskRepository->update($task, $task['id']);
            } else {
                $this->taskRepository->create($task);
            }
        }

        return $this->sendResponse(GroupTaskResource::make($groupTask), 'GroupTask updated successfully');
    }

    /**
     * Remove the specified GroupTask from storage.
     * DELETE /groupTasks/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        /** @var GroupTask $groupTask */
        $groupTask = $this->groupTaskRepository->find($id);

        if (empty($groupTask)) {
            return $this->sendError('Group Task not found');
        }

        $groupTask->tasks()->forceDelete();

        PurchaseOrderHasGroupTask::where('group_task_id', $id)->forceDelete();
        PurchaseOrderHasTask::where('group_task_id', $id)->forceDelete();
        OutlineAgreementHasGroupTask::where('group_task_id', $id)->forceDelete();
        WorkOrderHasGroupTask::where('group_task_id', $id)->forceDelete();
        WorkOrderHasTask::where('group_task_id', $id)->forceDelete();

        $groupTask->forceDelete();

        return $this->sendSuccess('Group Task deleted successfully');
    }

    public function nameList()
    {
        $nameList = GroupTask::pluck('group_task_name')->toArray();

        $nameListFormatted = array_map(function ($value) {
            return ['value' => $value, 'label' => $value];
        }, $nameList);

        return $this->sendResponse($nameListFormatted, 'Group Task Name List retrieved successfully.');
    }
}
