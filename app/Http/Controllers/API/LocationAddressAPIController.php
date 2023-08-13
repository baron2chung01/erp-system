<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateLocationAddressAPIRequest;
use App\Http\Requests\API\UpdateLocationAddressAPIRequest;
use App\Models\LocationAddress;
use App\Repositories\LocationAddressRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class LocationAddressAPIController
 */
class LocationAddressAPIController extends AppBaseController
{
private LocationAddressRepository $locationAddressRepository;

    public function __construct(LocationAddressRepository $locationAddressRepo)
    {
        $this->locationAddressRepository = $locationAddressRepo;
    }

    /**
     * Display a listing of the LocationAddresses.
     * GET|HEAD /location-addresses
     */
    public function index(Request $request): JsonResponse
    {
        $locationAddresses = $this->locationAddressRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($locationAddresses->toArray(), 'Location Addresses retrieved successfully');
    }

    /**
     * Store a newly created LocationAddress in storage.
     * POST /location-addresses
     */
    public function store(Request $request): JsonResponse
    {
        $input = $request->all();

        if (isset($request->addresses)) {
            foreach ($input['addresses'] as $address) {
                $locationAddress = $this->locationAddressRepository->create([
                    'location_id' => $input['location_id'],
                    'address'     => $address['address'],
                    'remark'      => $address['remark'],
                    'status'      => LocationAddress::ACTIVE,
                ]);
            }

        }

        return $this->sendResponse($locationAddress->toArray(), 'Location Address saved successfully');
    }

    /**
     * Display the specified LocationAddress.
     * GET|HEAD /location-addresses/{id}
     */
    public function show($id): JsonResponse
    {
        /** @var LocationAddress $locationAddress */
        $locationAddress = $this->locationAddressRepository->find($id);

        if (empty($locationAddress)) {
            return $this->sendError('Location Address not found');
        }

        return $this->sendResponse($locationAddress->toArray(), 'Location Address retrieved successfully');
    }

    /**
     * Update the specified LocationAddress in storage.
     * PUT/PATCH /location-addresses/{id}
     */
    public function update($id, UpdateLocationAddressAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var LocationAddress $locationAddress */
        $locationAddress = $this->locationAddressRepository->find($id);

        if (empty($locationAddress)) {
            return $this->sendError('Location Address not found');
        }

        $locationAddress = $this->locationAddressRepository->update($input, $id);

        return $this->sendResponse($locationAddress->toArray(), 'LocationAddress updated successfully');
    }

    /**
     * Remove the specified LocationAddress from storage.
     * DELETE /location-addresses/{id}
     *
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        /** @var LocationAddress $locationAddress */
        $locationAddress = $this->locationAddressRepository->find($id);

        if (empty($locationAddress)) {
            return $this->sendError('Location Address not found');
        }

        $locationAddress->delete();

        return $this->sendSuccess('Location Address deleted successfully');
    }
}
