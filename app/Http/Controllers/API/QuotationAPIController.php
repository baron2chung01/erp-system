<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateQuotationAPIRequest;
use App\Http\Requests\API\UpdateQuotationAPIRequest;
use App\Http\Resources\QuotationResource;
use App\Models\Quotation;
use App\Repositories\QuotationRepository;
use App\Traits\Arr;
use Illuminate\Http\Request;
use Response;

/**
 * Class QuotationController
 * @package App\Http\Controllers\API
 */
class QuotationAPIController extends AppBaseController
{
    /** @var  QuotationRepository */
    private $quotationRepository;

    public function __construct(QuotationRepository $quotationRepo)
    {
        $this->quotationRepository = $quotationRepo;
    }

    /**
     * Display a listing of the Quotation.
     * GET|HEAD /quotations
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        list($input, $current, $pageSize) = $this->getInput($request);

        $checkEmpty = $request->all();
        if (empty($checkEmpty)) {
            return response()->json($this->quotationRepository->all([
                'status' => Quotation::ACTIVE,
            ])->pluck('name', 'id'));
        }

        $quotations = $this->quotationRepository->all(
            $input,
            $current,
            $pageSize
        );

        $total = count($this->quotationRepository->all(
            $input
        ));

        return $this->sendResponse([
            'data'  => QuotationResource::collection($quotations),
            'total' => $total,
        ], 'Quotations retrieved successfully');
    }

    /**
     * Store a newly created Quotation in storage.
     * POST /quotations
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        $quotation = $this->quotationRepository->create($input);

        return $this->sendResponse(QuotationResource::make($quotation), 'Quotation saved successfully');
    }

    /**
     * Display the specified Quotation.
     * GET|HEAD /quotations/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Quotation $quotation */
        $quotation = $this->quotationRepository->find($id);

        if (empty($quotation)) {
            return $this->sendError('Quotation not found');
        }

        return $this->sendResponse(QuotationResource::make($quotation), 'Quotation retrieved successfully');
    }

    /**
     * Update the specified Quotation in storage.
     * PUT/PATCH /quotations/{id}
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

        /** @var Quotation $quotation */
        $quotation = $this->quotationRepository->find($id);

        if (empty($quotation)) {
            return $this->sendError('Quotation not found');
        }

        $quotation = $this->quotationRepository->update($input, $id);

        return $this->sendResponse(QuotationResource::make($quotation), 'Quotation updated successfully');
    }

    /**
     * Remove the specified Quotation from storage.
     * DELETE /quotations/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        /** @var Quotation $quotation */
        $quotation = $this->quotationRepository->find($id);

        if (empty($quotation)) {
            return $this->sendError('Quotation not found');
        }

        $quotation->delete();

        return $this->sendSuccess('Quotation deleted successfully');
    }
}
