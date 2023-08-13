<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateOfficialReceiptAPIRequest;
use App\Http\Requests\API\UpdateOfficialReceiptAPIRequest;
use App\Http\Resources\OfficialReceiptResource;
use App\Models\OfficialReceipt;
use App\Repositories\OfficialReceiptRepository;
use App\Traits\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;

/**
 * Class OfficialReceiptController
 * @package App\Http\Controllers\API
 */
class OfficialReceiptAPIController extends AppBaseController
{
    /** @var  OfficialReceiptRepository */
    private $officialReceiptRepository;

    public function __construct(OfficialReceiptRepository $officialReceiptRepo)
    {
        $this->officialReceiptRepository = $officialReceiptRepo;
    }

    /**
     * Display a listing of the OfficialReceipt.
     * GET|HEAD /officialReceipts
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        list($input, $current, $pageSize) = $this->getInput($request);

        $officialReceipts = $this->officialReceiptRepository->all(
            $input,
            $current,
            $pageSize
        );

        $total = count($this->officialReceiptRepository->all(
            $input
        ));

        return $this->sendResponse([
            'data'  => OfficialReceiptResource::collection($officialReceipts),
            'total' => $total
        ], 'Official Receipts retrieved successfully');
    }

    /**
     * Store a newly created OfficialReceipt in storage.
     * POST /officialReceipts
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        $officialReceipt = $this->officialReceiptRepository->create($input);

        return $this->sendResponse($officialReceipt->toArray(), 'Official Receipt saved successfully');
    }

    /**
     * Display the specified OfficialReceipt.
     * GET|HEAD /officialReceipts/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var OfficialReceipt $officialReceipt */
        $officialReceipt = $this->officialReceiptRepository->find($id);

        if (empty($officialReceipt)) {
            return $this->sendError('Official Receipt not found');
        }

        return $this->sendResponse($officialReceipt->toArray(), 'Official Receipt retrieved successfully');
    }

    /**
     * Update the specified OfficialReceipt in storage.
     * PUT/PATCH /officialReceipts/{id}
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

        /** @var OfficialReceipt $officialReceipt */
        $officialReceipt = $this->officialReceiptRepository->find($id);

        if (empty($officialReceipt)) {
            return $this->sendError('Official Receipt not found');
        }

        $officialReceipt = $this->officialReceiptRepository->update($input, $id);

        return $this->sendResponse($officialReceipt->toArray(), 'OfficialReceipt updated successfully');
    }

    /**
     * Remove the specified OfficialReceipt from storage.
     * DELETE /officialReceipts/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        /** @var OfficialReceipt $officialReceipt */
        $officialReceipt = $this->officialReceiptRepository->find($id);

        if (empty($officialReceipt)) {
            return $this->sendError('Official Receipt not found');
        }

        $officialReceipt->delete();

        return $this->sendSuccess('Official Receipt deleted successfully');
    }
}
