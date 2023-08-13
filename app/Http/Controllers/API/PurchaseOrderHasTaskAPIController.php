<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Models\PurchaseOrderHasTask;
use App\Repositories\PurchaseOrderHasTaskRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class PurchaseOrderHasTaskAPIController
 */
class PurchaseOrderHasTaskAPIController extends AppBaseController
{
    private PurchaseOrderHasTaskRepository $purchaseOrderHasTaskRepository;

    public function __construct(PurchaseOrderHasTaskRepository $purchaseOrderHasTaskRepo)
    {
        $this->purchaseOrderHasTaskRepository = $purchaseOrderHasTaskRepo;
    }

    /**
     * Display a listing of the PurchaseOrderHasTasks.
     * GET|HEAD /purchase-order-has-tasks
     */
    public function index(Request $request): JsonResponse
    {
        $purchaseOrderHasTasks = $this->purchaseOrderHasTaskRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($purchaseOrderHasTasks->toArray(), 'Purchase Order Has Tasks retrieved successfully');
    }

    /**
     * Remove the specified PurchaseOrderHasTask from storage.
     * DELETE /purchase-order-has-tasks/{id}
     *
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        /** @var PurchaseOrderHasTask $purchaseOrderHasTask */
        $purchaseOrderHasTask = $this->purchaseOrderHasTaskRepository->find($id);

        if (empty($purchaseOrderHasTask)) {
            return $this->sendError('Purchase Order Has Task not found');
        }

        $purchaseOrderHasTask->delete();

        return $this->sendSuccess('Purchase Order Has Task deleted successfully');
    }
}
