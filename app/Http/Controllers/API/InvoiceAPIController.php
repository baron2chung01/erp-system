<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateInvoiceAPIRequest;
use App\Http\Requests\API\UpdateInvoiceAPIRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use App\Traits\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;

/**
 * Class InvoiceController
 * @package App\Http\Controllers\API
 */
class InvoiceAPIController extends AppBaseController
{
    /** @var  InvoiceRepository */
    private $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepo)
    {
        $this->invoiceRepository = $invoiceRepo;
    }

    /**
     * Display a listing of the Invoice.
     * GET|HEAD /invoices
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        list($input, $current, $pageSize) = $this->getInput($request);

        $invoices = $this->invoiceRepository->all(
            $input,
            $current,
            $pageSize
        );

        $total = count($this->invoiceRepository->all(
            $input
        ));

        return $this->sendResponse([
            'data'  => InvoiceResource::collection($invoices),
            'total' => $total
        ], 'Invoices retrieved successfully');
    }

    /**
     * Store a newly created Invoice in storage.
     * POST /invoices
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $input = Arr::underscoreKeys($input);

        $invoice = $this->invoiceRepository->create($input);

        return $this->sendResponse($invoice->toArray(), 'Invoice saved successfully');
    }

    /**
     * Display the specified Invoice.
     * GET|HEAD /invoices/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Invoice $invoice */
        $invoice = $this->invoiceRepository->find($id);

        if (empty($invoice)) {
            return $this->sendError('Invoice not found');
        }

        return $this->sendResponse($invoice->toArray(), 'Invoice retrieved successfully');
    }

    /**
     * Update the specified Invoice in storage.
     * PUT/PATCH /invoices/{id}
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

        /** @var Invoice $invoice */
        $invoice = $this->invoiceRepository->find($id);

        if (empty($invoice)) {
            return $this->sendError('Invoice not found');
        }

        $invoice = $this->invoiceRepository->update($input, $id);

        return $this->sendResponse($invoice->toArray(), 'Invoice updated successfully');
    }

    /**
     * Remove the specified Invoice from storage.
     * DELETE /invoices/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        /** @var Invoice $invoice */
        $invoice = $this->invoiceRepository->find($id);

        if (empty($invoice)) {
            return $this->sendError('Invoice not found');
        }

        $invoice->delete();

        return $this->sendSuccess('Invoice deleted successfully');
    }
}
