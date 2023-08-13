<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateLocationAPIRequest;
use App\Http\Requests\API\UpdateLocationAPIRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Models\LocationAddress;
use App\Repositories\LocationRepository;
use App\Traits\Arr;
use Illuminate\Http\Request;
use Response;

/**
 * Class LocationController
 * @package App\Http\Controllers\API
 */
class LocationAPIController extends AppBaseController
{
    /** @var  LocationRepository */
    private $locationRepository;

    public function __construct(LocationRepository $locationRepo)
    {
        $this->locationRepository = $locationRepo;
    }

    /**
     * Display a listing of the Location.
     * GET|HEAD /locations
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->authorize('location_view');

        list($input, $current, $pageSize) = $this->getInput($request);

        $checkAll = $request->get('all');
        if (isset($checkAll) && $checkAll) {
            $purchaseOrderId = $request->get('purchase_order_id');
            return response()->json($this->locationRepository->all([
                'purchase_order_id' => $purchaseOrderId,
            ])->pluck('name', 'id'));
        }

        if (empty($request->all())) {
            return response()->json($this->locationRepository->all([
                'status' => Location::ACTIVE,
            ])->pluck('location_name', 'id'));
        }

        if (isset($input['search'])) {
            $query = Location::where('code', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('address', 'LIKE', '%' . $input['search'] . '%')
                ->orWhere('location_name', 'LIKE', '%' . $input['search'] . '%');

            $total = $query->get()->count();

            $locations = $query->skip($current)
                ->take($pageSize)
                ->get();

        } else {

            $locations = $this->locationRepository->all(
                $input,
                $current,
                $pageSize
            );

            $total = count($this->locationRepository->all(
                $input
            ));
        }

        return $this->sendResponse([
            'data'  => LocationResource::collection($locations),
            'total' => $total,
        ], 'Locations retrieved successfully');
    }

    /**
     * Store a newly created Location in storage.
     * POST /locations
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->authorize('location_create');

        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        $location = $this->locationRepository->create($input);

        return $this->sendResponse(LocationResource::make($location), 'Location saved successfully');
    }

    /**
     * Display the specified Location.
     * GET|HEAD /locations/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('location_view');

        /** @var Location $location */
        $location = $this->locationRepository->find($id);

        if (empty($location)) {
            return $this->sendError('Location not found');
        }

        return $this->sendResponse(LocationResource::make($location), 'Location retrieved successfully');
    }

    /**
     * Update the specified Location in storage.
     * PUT/PATCH /locations/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $this->authorize('location_update');

        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        /** @var Location $location */
        $location = $this->locationRepository->find($id);

        if (empty($location)) {
            return $this->sendError('Location not found');
        }

        if (isset($request->addresses)) {
            foreach ($input['addresses'] as $address) {
                LocationAddress::find($address['id'])->update([
                    'address' => $address['address'],
                    'remark'  => $address['remark'],
                ]);
            }
        }

        $location = $this->locationRepository->update($input, $id);

        return $this->sendResponse(LocationResource::make($location), 'Location updated successfully');
    }

    /**
     * Remove the specified Location from storage.
     * DELETE /locations/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        $this->authorize('location_delete');

        /** @var Location $location */
        $location = $this->locationRepository->find($id);

        if (empty($location)) {
            return $this->sendError('Location not found');
        }

        $location->delete();

        return $this->sendSuccess('Location deleted successfully');
    }
}
