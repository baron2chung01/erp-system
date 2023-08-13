<?php

namespace App\Http\Resources;

use App\Http\Resources\POHasSubcontractorTaskResource;
use App\Http\Resources\SubcontractorPODataResource;
use App\Http\Resources\SupplierPODataResource;
use App\Models\Location;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'                      => $this->id,
            'location_id'             => $this->location_id,
            'location_data'           => [
                'location_name' => Location::find($this->location_id) ? Location::find($this->location_id)->location_name : null,
                'location_pic'  => Location::find($this->location_id) ? Location::find($this->location_id)->contact_name : null,
                'location_code' => Location::find($this->location_id) ? Location::find($this->location_id)->code : null,
            ],
            'po_number'               => $this->po_number,
            'name'                    => $this->name,
            'display_name'            => $this->display_name,
            'description'             => $this->description,
            'shipping_address'        => $this->shipping_address,
            'contact_name'            => $this->contact_name,
            'is_contract'             => $this->is_contract,
            'status'                  => $this->status,
            'display_status'          => PurchaseOrder::CONTRACT_STATUS[$this->status],
            'quotation_remark'        => $this->quotation_remark,
            'quotations'              => AssetResource::collection($this->quotations),
            'supplier_pos'            => AssetResource::collection($this->supplierPOs),
            'invoices'                => AssetResource::collection($this->invoices),
            'receipts'                => AssetResource::collection($this->receipts),
            'signed_quotations'       => AssetResource::collection($this->signedQuotations),
            'signed_supplier_pos'     => AssetResource::collection($this->signedSupplierPOs),
            'signed_invoices'         => AssetResource::collection($this->signedInvoices),
            'signed_receipts'         => AssetResource::collection($this->signedReceipts),
            'outline_agreement'       => OutlineAgreementResource::make($this->whenLoaded('outlineAgreement')),
            'group_tasks'             => GroupTaskResource::collection($this->whenLoaded('poHasGroupTasks')),
            'client_id'               => count($this->clients) ? $this->clients[0]->id : null,
            'client_name'             => count($this->clients) ? $this->clients[0]->name : null,
            'contact_people_id'       => count($this->contactPeople) ? $this->contactPeople->pluck('id') : [],
            'suppliers_id'            => count($this->suppliers) ? $this->suppliers->pluck('id') : [],
            'subcontractors_id'       => count($this->subcontractors) ? $this->subcontractors->pluck('id') : [],
            'products'                => POSupplierProductResource::collection($this->products)->groupBy('supplier_id'),
            'suppliers'               => $this->suppliers,
            'subcontractors'          => SubcontractorResource::collection($this->subcontractors),
            'issued_at'               => $this->issued_at,
            'received_at'             => $this->received_at,
            'date_to_AC_dept'         => $this->date_to_AC_dept,
            'qty'                     => $this->qty,
            'expect_delivered_at'     => isset($this->expect_delivered_at) ? Carbon::parse($this->expect_delivered_at)->format('Y-m-d') : null,
            'expect_completed_at'     => isset($this->expect_completed_at) ? Carbon::parse($this->expect_completed_at)->format('Y-m-d') : null,
            'actual_completed_at'     => isset($this->actual_completed_at) ? Carbon::parse($this->actual_completed_at)->format('Y-m-d') : null,
            'person_in_charge'        => $this->person_in_charge,
            'invoice_date'            => $this->invoice_date,
            'invoice_no'              => $this->invoice_no,
            'quot_ref_prefix'         => $this->location ? $this->location->code . '-Q-' : 'Please enter location-Q-',
            'quot_ref'                => $this->quot_ref,
            'quot_date'               => isset($this->quot_date) ? Carbon::parse($this->quot_date)->format('Y-m-d') : null,
            'discount_type'           => $this->discount_type,
            'discount_value'          => $this->discount_value,
            'total_price'             => $this->total_price,
            'task_info'               => $this->relationLoaded('taskInfo') ? POHasTaskResource::collection($this->taskInfo)->groupBy('group_task_id') : $this->whenLoaded('taskInfo'),
            'subcontractor_task_info' => $this->relationLoaded('subcontractorTaskInfo') ? POHasSubcontractorTaskResource::collection($this->subcontractorTaskInfo()->with('priceInfo')->get())->groupBy('group_task_id') : $this->whenLoaded('subcontractorTaskInfo'),
            'supplier_po_data'        => $this->relationLoaded('supplierPOData') ? SupplierPODataResource::collection($this->supplierPOData) : null,
            'subcontractor_po_data'   => $this->relationLoaded('subcontractorPOData') ? SubcontractorPODataResource::collection($this->subcontractorPOData) : null,
        ];
    }
}
