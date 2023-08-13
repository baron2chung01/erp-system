<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Models\PurchaseOrderHasSubcontractorTask;
use App\Repositories\PurchaseOrderHasSubcontractorTaskRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class PurchaseOrderHasSubcontractorTaskAPIController
 */
class PurchaseOrderHasSubcontractorTaskAPIController extends AppBaseController
{
    private PurchaseOrderHasSubcontractorTaskRepository $purchaseOrderHasSubcontractorTaskRepository;

    public function __construct(PurchaseOrderHasSubcontractorTaskRepository $purchaseOrderHasSubcontractorTaskRepo)
    {
        $this->purchaseOrderHasSubcontractorTaskRepository = $purchaseOrderHasSubcontractorTaskRepo;
    }

    /**
     * Display a listing of the PurchaseOrderHasSubcontractorTasks.
     * GET|HEAD /purchase-order-has-subcontractor-tasks
     */
    public function index(Request $request): JsonResponse
    {
        $purchaseOrderHasSubcontractorTasks = $this->purchaseOrderHasSubcontractorTaskRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($purchaseOrderHasSubcontractorTasks->toArray(), 'Purchase Order Has Subcontractor Tasks retrieved successfully');
    }

    /**
     * Remove the specified PurchaseOrderHasSubcontractorTask from storage.
     * DELETE /purchase-order-has-subcontractor-tasks/{id}
     *
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        /** @var PurchaseOrderHasSubcontractorTask $PurchaseOrderHasSubcontractorTask */
        $PurchaseOrderHasSubcontractorTask = $this->purchaseOrderHasSubcontractorTaskRepository->find($id);

        if (empty($PurchaseOrderHasSubcontractorTask)) {
            return $this->sendError('Purchase Order Has Subcontractor Task not found');
        }

        $PurchaseOrderHasSubcontractorTask->delete();

        return $this->sendSuccess('Purchase Order Has Subcontractor Task deleted successfully');
    }
}
