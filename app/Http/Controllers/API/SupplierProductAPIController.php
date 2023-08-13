<?php

namespace App\Http\Controllers\API;

use App\Traits\Arr;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\SupplierProduct;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\AppBaseController;
use App\Repositories\SupplierProductRepository;
use App\Http\Requests\API\CreateSupplierProductAPIRequest;
use App\Http\Requests\API\UpdateSupplierProductAPIRequest;

/**
 * Class SupplierProductAPIController
 */
class SupplierProductAPIController extends AppBaseController
{
    private SupplierProductRepository $supplierProductRepository;

    public function __construct(SupplierProductRepository $supplierProductRepo)
    {
        $this->supplierProductRepository = $supplierProductRepo;
    }

    /**
     * Display a listing of the SupplierProducts.
     * GET|HEAD /supplier-products
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('supplier_view');

        $supplierProducts = SupplierProduct::all();

        return $this->sendResponse($supplierProducts->toArray(), 'Supplier Products retrieved successfully');
    }

    /**
     * Store a newly created SupplierProduct in storage.
     * POST /supplier-products
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('supplier_create');

        foreach ($request->products as $product){
            $this->supplierProductRepository->create([
                'supplier_id' => $request->supplier_id,
                'name' => $product['name'],
                'desc' => $product['desc'],
                'remark' => $product['remark'] ?? null,
            ]);
        }

        return $this->sendResponse(SupplierProduct::all(), 'Supplier Product saved successfully');
    }

    /**
     * Display the specified SupplierProduct.
     * GET|HEAD /supplier-products/{id}
     */
    public function show($id): JsonResponse
    {
        $this->authorize('supplier_view');

        /** @var SupplierProduct $supplierProduct */
        $supplierProduct = $this->supplierProductRepository->find($id);

        if (empty($supplierProduct)) {
            return $this->sendError('Supplier Product not found');
        }

        return $this->sendResponse($supplierProduct->toArray(), 'Supplier Product retrieved successfully');
    }

    /**
     * Update the specified SupplierProduct in storage.
     * PUT/PATCH /supplier-products/{id}
     */
    public function update($id, UpdateSupplierProductAPIRequest $request): JsonResponse
    {
        $this->authorize('supplier_update');

        $input = $request->all();

        /** @var SupplierProduct $supplierProduct */
        $supplierProduct = $this->supplierProductRepository->find($id);

        if (empty($supplierProduct)) {
            return $this->sendError('Supplier Product not found');
        }

        $supplierProduct = $this->supplierProductRepository->update($input, $id);

        return $this->sendResponse($supplierProduct->toArray(), 'SupplierProduct updated successfully');
    }

    /**
     * Remove the specified SupplierProduct from storage.
     * DELETE /supplier-products/{id}
     *
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        $this->authorize('supplier_delete');

        /** @var SupplierProduct $supplierProduct */
        $supplierProduct = $this->supplierProductRepository->find($id);

        if (empty($supplierProduct)) {
            return $this->sendError('Supplier Product not found');
        }

        $supplierProduct->delete();

        return $this->sendSuccess('Supplier Product deleted successfully');
    }

    public function productNameList($id)
    {
        $this->authorize('supplier_view');

        $list = Supplier::find($id)->products->pluck('name', 'id');

        return response()->json($list);
    }
}
