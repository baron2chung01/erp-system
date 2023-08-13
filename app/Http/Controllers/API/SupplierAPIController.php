<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSupplierAPIRequest;
use App\Http\Requests\API\UpdateSupplierAPIRequest;
use App\Http\Resources\SupplierResource;
use App\Models\SubcontractorHasTask;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\Task;
use App\Repositories\SupplierRepository;
use App\Traits\Arr;
use Illuminate\Http\Request;
use Response;

/**
 * Class SupplierController
 * @package App\Http\Controllers\API
 */
class SupplierAPIController extends AppBaseController
{
    /** @var  SupplierRepository */
    private $supplierRepository;

    public function __construct(SupplierRepository $supplierRepo)
    {
        $this->supplierRepository = $supplierRepo;
    }

    /**
     * Display a listing of the Supplier.
     * GET|HEAD /suppliers
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->authorize('supplier_view');

        list($input, $current, $pageSize) = $this->getInput($request);

        if (empty($request->all())) {
            return response()->json($this->supplierRepository->all([
                'status' => Supplier::ACTIVE,
            ])->pluck('name', 'id'));
        }

        if (isset($input['search'])) {
            $query = Supplier::where('name', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('email', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('address', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('phone', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('contact_person', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('payment_term', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('delivery_mode', 'LIKE', '%' . $input['search'] . '%');

            $total = $query->get()->count();

            $roles = $query->skip($current)
                ->take($pageSize)
                ->get();

        } else {

            $suppliers = $this->supplierRepository->all(
                $input,
                $current,
                $pageSize
            );

            $total = count($this->supplierRepository->all(
                $input
            ));
        }

        return $this->sendResponse([
            'data'  => SupplierResource::collection($suppliers),
            'total' => $total,
        ], 'Suppliers retrieved successfully');
    }

    /**
     * Store a newly created Supplier in storage.
     * POST /suppliers
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->authorize('supplier_create');

        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        $supplier = $this->supplierRepository->create($input);

        return $this->sendResponse(SupplierResource::make($supplier), 'Supplier saved successfully');
    }

    /**
     * Display the specified Supplier.
     * GET|HEAD /suppliers/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('supplier_view');

        /** @var Supplier $supplier */
        $supplier = $this->supplierRepository->find($id);

        if (empty($supplier)) {
            return $this->sendError('Supplier not found');
        }

        return $this->sendResponse(SupplierResource::make($supplier), 'Supplier retrieved successfully');
    }

    /**
     * Update the specified Supplier in storage.
     * PUT/PATCH /suppliers/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $this->authorize('supplier_update');

        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        /** @var Supplier $supplier */
        $supplier = $this->supplierRepository->find($id);

        if (empty($supplier)) {
            return $this->sendError('Supplier not found');
        }

        $supplier = $this->supplierRepository->update($input, $id);

        return $this->sendResponse(SupplierResource::make($supplier), 'Supplier updated successfully');
    }

    public function updateProducts($id, Request $request)
    {
        $this->authorize('supplier_update');

        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        /** @var Supplier $supplier */
        $supplier = $this->supplierRepository->find($id);

        if (empty($supplier)) {
            return $this->sendError('Supplier not found');
        }

        if (isset($request->products)) {

            $oriIDList = $supplier->products->pluck('id')->toArray();

            foreach ($input['products'] as $product) {
                // handle update
                if (isset($product['id']) && in_array($product['id'], $oriIDList)) {
                    SupplierProduct::find($product['id'])->update([
                        'supplier_id' => $id,
                        'desc'        => $product['desc'],
                        'name'        => $product['name'],
                        'remark'      => $product['remark'] ?? null,
                        'qty'         => $product['qty'] ?? null,
                        'unit'        => $product['unit'] ?? null,
                        'unit_price'  => $product['unit_price'] ?? null,
                        'total_price' => $product['total_price'] ?? null,
                        'product_no'  => $product['product_no'] ?? null,
                        'status'      => $product['status'] ?? SupplierProduct::ACTIVE,
                    ]);
                } else {
                    // handle create
                    SupplierProduct::create([
                        'supplier_id' => $id,
                        'desc'        => $product['desc'],
                        'name'        => $product['name'],
                        'remark'      => $product['remark'] ?? null,
                        'qty'         => $product['qty'] ?? null,
                        'unit'        => $product['unit'] ?? null,
                        'unit_price'  => $product['unit_price'] ?? null,
                        'total_price' => $product['total_price'] ?? null,
                        'product_no'  => $product['product_no'] ?? null,
                        'status'      => $product['status'] ?? SupplierProduct::ACTIVE,
                    ]);
                }
            }

            // handle delete
            $inputIDList = collect($request->products)->pluck('id')->toArray();
            $deleteIDList = array_diff($oriIDList, $inputIDList);
            foreach ($deleteIDList as $deleteId) {
                SupplierProduct::find($deleteId)->forceDelete();
            }
        }
        return $this->sendResponse(SupplierResource::make($supplier), 'Supplier products updated successfully');

    }

    /**
     * Remove the specified Supplier from storage.
     * DELETE /suppliers/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        $this->authorize('supplier_delete');

        /** @var Supplier $supplier */
        $supplier = $this->supplierRepository->find($id);

        if (empty($supplier)) {
            return $this->sendError('Supplier not found');
        }

        $supplier->delete();

        return $this->sendSuccess('Supplier deleted successfully');
    }

}
