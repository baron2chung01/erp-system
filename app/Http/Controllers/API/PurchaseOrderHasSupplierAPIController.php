<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Models\ProductPriceHistory;
use App\Models\PurchaseOrderHasSupplier;
use App\Models\SupplierProduct;
use App\Repositories\PurchaseOrderHasSupplierRepository;
use App\Traits\Arr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class PurchaseOrderHasSupplierAPIController
 */
class PurchaseOrderHasSupplierAPIController extends AppBaseController
{
    private PurchaseOrderHasSupplierRepository $purchaseOrderHasSupplierRepository;

    public function __construct(PurchaseOrderHasSupplierRepository $purchaseOrderHasSupplierRepo)
    {
        $this->purchaseOrderHasSupplierRepository = $purchaseOrderHasSupplierRepo;
    }

    /**
     * Display a listing of the PurchaseOrderHasSuppliers.
     * GET|HEAD /purchase-order-has-suppliers
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('purchase_order_view');

        $purchaseOrderHasSuppliers = $this->purchaseOrderHasSupplierRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($purchaseOrderHasSuppliers->toArray(), 'Purchase Order Has Suppliers retrieved successfully');
    }

    /**
     * Store a newly created PurchaseOrderHasSupplier in storage.
     * POST /purchase-order-has-suppliers
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('purchase_order_create');

        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        $result = collect([]);

        if (!isset($request->products)) {
            return $this->sendError('No product is returned.');
        }

        foreach ($input['products'] as $product) {
            $productQuery = SupplierProduct::find($product['id']);
            $savedProduct = PurchaseOrderHasSupplier::create([
                'purchase_order_id' => $request->purchaseOrderId ?? $request->id,
                'product_id'        => $product['id'],
                'product_name'      => $productQuery->name,
                'product_desc'      => $product['desc'] ?? $productQuery->desc,
                'supplier_id'       => $request->supplier_id ?? $product['supplier_id'],
                'qty'               => $product['qty'],
                'unit_price'        => $product['unit_price'],
            ]);

            ProductPriceHistory::create([
                'product_id' => $savedProduct->id,
                'unit_price' => $savedProduct->unit_price,
            ]);

            $result->push($savedProduct);
        }

        return $this->sendResponse($result->toArray(), 'Purchase Order Has Supplier saved successfully');
    }

    /**
     * Display the specified PurchaseOrderHasSupplier.
     * GET|HEAD /purchase-order-has-suppliers/{id}
     */
    public function show($id): JsonResponse
    {
        $this->authorize('purchase_order_view');

        /** @var PurchaseOrderHasSupplier $purchaseOrderHasSupplier */
        $purchaseOrderHasSupplier = $this->purchaseOrderHasSupplierRepository->find($id);

        if (empty($purchaseOrderHasSupplier)) {
            return $this->sendError('Purchase Order Has Supplier not found');
        }

        return $this->sendResponse($purchaseOrderHasSupplier->toArray(), 'Purchase Order Has Supplier retrieved successfully');
    }

    /**
     * Update the specified PurchaseOrderHasSupplier in storage.
     * PUT/PATCH /purchase-order-has-suppliers/{id}
     */
    public function update($id, Request $request): JsonResponse
    {
        $this->authorize('purchase_order_update');

        $input = $request->all();

        /** @var PurchaseOrderHasSupplier $purchaseOrderHasSupplier */
        $purchaseOrderHasSupplier = $this->purchaseOrderHasSupplierRepository->find($id);

        if (empty($purchaseOrderHasSupplier)) {
            return $this->sendError('Purchase Order Has Supplier not found');
        }

        $purchaseOrderHasSupplier = $this->purchaseOrderHasSupplierRepository->update($input, $id);

        return $this->sendResponse($purchaseOrderHasSupplier->toArray(), 'PurchaseOrderHasSupplier updated successfully');
    }

    // public function updateAll(Request $request): JsonResponse
    // {
    //     $result = collect([]);

    //     foreach ($request->products as $product) {
    //         $productQuery = SupplierProduct::find($product['id']);
    //         $savedProduct = PurchaseOrderHasSupplier::updateOrCreate([
    //             'purchase_order_id' => $request->purchaseOrderId,
    //             'product_id'        => $product['id'],
    //         ], [
    //             'product_name' => $productQuery->name,
    //             'product_desc' => $product['desc'] ?? $productQuery->desc,
    //             'supplier_id'  => $request->supplier_id,
    //             'qty'          => $product['qty'],
    //             'unit_price'   => $product['unitPrice'],
    //         ]);

    //         ProductPriceHistory::create([
    //             'product_id' => $savedProduct->id,
    //             'unit_price' => $savedProduct->unit_price,
    //         ]);

    //         $result->push($savedProduct);
    //     }

    //     return $this->sendResponse($purchaseOrderHasSupplier->toArray(), 'PurchaseOrderHasSupplier updated successfully');
    // }

    /**
     * Remove the specified PurchaseOrderHasSupplier from storage.
     * DELETE /purchase-order-has-suppliers/{id}
     *
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        $this->authorize('purchase_order_delete');

        /** @var PurchaseOrderHasSupplier $purchaseOrderHasSupplier */
        $purchaseOrderHasSupplier = $this->purchaseOrderHasSupplierRepository->find($id);

        if (empty($purchaseOrderHasSupplier)) {
            return $this->sendError('Purchase Order Has Supplier not found');
        }

        $purchaseOrderHasSupplier->delete();

        return $this->sendSuccess('Purchase Order Has Supplier deleted successfully');
    }
}
