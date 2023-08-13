<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Models\WorkOrderHasTask;
use App\Repositories\WorkOrderHasTaskRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class WorkOrderHasTaskAPIController
 */
class WorkOrderHasTaskAPIController extends AppBaseController
{
    private WorkOrderHasTaskRepository $workOrderHasTaskRepository;

    public function __construct(WorkOrderHasTaskRepository $workOrderHasTaskRepo)
    {
        $this->workOrderHasTaskRepository = $workOrderHasTaskRepo;
    }

    /**
     * Display a listing of the WorkOrderHasTasks.
     * GET|HEAD /work-order-has-tasks
     */
    public function index(Request $request): JsonResponse
    {
        $workOrderHasTasks = $this->workOrderHasTaskRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($workOrderHasTasks->toArray(), 'Work Order Has Tasks retrieved successfully');
    }

    /**
     * Remove the specified WorkOrderHasTask from storage.
     * DELETE /work-order-has-tasks/{id}
     *
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        /** @var WorkOrderHasTask $workOrderHasTask */
        $workOrderHasTask = $this->workOrderHasTaskRepository->find($id);

        if (empty($workOrderHasTask)) {
            return $this->sendError('Work Order Has Task not found');
        }

        $workOrderHasTask->delete();

        return $this->sendSuccess('Work Order Has Task deleted successfully');
    }
}
