<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateIbeaconAPIRequest;
use App\Http\Requests\API\UpdateIbeaconAPIRequest;
use App\Http\Resources\IbeaconResource;
use App\Models\Ibeacon;
use App\Repositories\IbeaconRepository;
use App\Traits\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;

/**
 * Class IbeaconController
 * @package App\Http\Controllers\API
 */

class IbeaconAPIController extends AppBaseController
{
    /** @var  IbeaconRepository */
    private $ibeaconRepository;

    public function __construct(IbeaconRepository $ibeaconRepo)
    {
        $this->ibeaconRepository = $ibeaconRepo;
    }

    /**
     * Display a listing of the Ibeacon.
     * GET|HEAD /ibeacons
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        list($input, $current, $pageSize) = $this->getInput($request);

        $ibeacons = $this->ibeaconRepository->all(
            $input,
            $current,
            $pageSize
        );

        $total = count($this->ibeaconRepository->all(
            $input
        ));

        return $this->sendResponse([
            'data' => IbeaconResource::collection($ibeacons),
            'total' => $total
        ], 'Ibeacons retrieved successfully');
    }

    /**
     * Store a newly created Ibeacon in storage.
     * POST /ibeacons
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        $ibeacon = $this->ibeaconRepository->create($input);

        return $this->sendResponse(IbeaconResource::make($ibeacon), 'Ibeacon saved successfully');
    }

    /**
     * Display the specified Ibeacon.
     * GET|HEAD /ibeacons/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Ibeacon $ibeacon */
        $ibeacon = $this->ibeaconRepository->find($id);

        if (empty($ibeacon)) {
            return $this->sendError('Ibeacon not found');
        }

        return $this->sendResponse(IbeaconResource::make($ibeacon), 'Ibeacon retrieved successfully');
    }

    /**
     * Update the specified Ibeacon in storage.
     * PUT/PATCH /ibeacons/{id}
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

        /** @var Ibeacon $ibeacon */
        $ibeacon = $this->ibeaconRepository->find($id);

        if (empty($ibeacon)) {
            return $this->sendError('Ibeacon not found');
        }

        $ibeacon = $this->ibeaconRepository->update($input, $id);

        return $this->sendResponse(IbeaconResource::make($ibeacon), 'Ibeacon updated successfully');
    }

    /**
     * Remove the specified Ibeacon from storage.
     * DELETE /ibeacons/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Ibeacon $ibeacon */
        $ibeacon = $this->ibeaconRepository->find($id);

        if (empty($ibeacon)) {
            return $this->sendError('Ibeacon not found');
        }

        $ibeacon->delete();

        return $this->sendSuccess('Ibeacon deleted successfully');
    }
}
