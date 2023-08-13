<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateTaskAPIRequest;
use App\Http\Requests\API\UpdateTaskAPIRequest;
use App\Http\Resources\TaskResource;
use App\Models\OutlineAgreementHasGroupTask;
use App\Models\PurchaseOrderHasGroupTask;
use App\Models\Task;
use App\Models\WorkOrderHasGroupTask;
use App\Repositories\TaskRepository;
use Illuminate\Http\Request;
use Response;

/**
 * Class TaskController
 * @package App\Http\Controllers\API
 */
class TaskAPIController extends AppBaseController
{
    /** @var  TaskRepository */
    private $taskRepository;

    public function __construct(TaskRepository $taskRepo)
    {
        $this->taskRepository = $taskRepo;
    }

    /**
     * Display a listing of the Task.
     * GET|HEAD /tasks
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        list($input, $current, $pageSize) = $this->getInput($request);

        $tasks = $this->taskRepository->all(
            $input,
            $current,
            $pageSize
        );

        $total = count($this->taskRepository->all(
            $input
        ));

        return $this->sendResponse([
            'data'  => TaskResource::collection($tasks),
            'total' => $total,
        ], 'Tasks retrieved successfully');
    }

    /**
     * Store a newly created Task in storage.
     * POST /tasks
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $task = $this->taskRepository->create($input);

        return $this->sendResponse($task->toArray(), 'Task saved successfully');
    }

    /**
     * Display the specified Task.
     * GET|HEAD /tasks/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Task $task */
        $tasks = $this->taskRepository->where('name', 'LIKE', '%' . $id . '%')->get();

        return $this->sendResponse(TaskResource::collection($tasks), 'Task retrieved successfully');
    }

    /**
     * Update the specified Task in storage.
     * PUT/PATCH /tasks/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $input = $request->all();

        /** @var Task $task */
        $task = $this->taskRepository->find($id);

        if (empty($task)) {
            return $this->sendError('Task not found');
        }

        $task = $this->taskRepository->update($input, $id);

        return $this->sendResponse($task->toArray(), 'Task updated successfully');
    }

    /**
     * Remove the specified Task from storage.
     * DELETE /tasks/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        /** @var Task $task */
        $task = $this->taskRepository->find($id);

        if (empty($task)) {
            return $this->sendError('Task not found');
        }

        if (count($task->groupTask->tasks) == 1) {
            // this is the last task to be deleted: also delete group task
            $groupTaskId = $task->groupTask->id;

            PurchaseOrderHasGroupTask::where('group_task_id', $groupTaskId)->forceDelete();
            OutlineAgreementHasGroupTask::where('group_task_id', $groupTaskId)->forceDelete();
            WorkOrderHasGroupTask::where('group_task_id', $groupTaskId)->forceDelete();

            $task->groupTask->forceDelete();

        }

        $task->delete();

        return $this->sendSuccess('Task deleted successfully');
    }
}
