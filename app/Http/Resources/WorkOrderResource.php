<?php

namespace App\Http\Resources;

use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
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
            'id'                   => $this->id,
            'wo_number'            => $this->wo_number,
            'purchase_order'       => PurchaseOrderResource::make($this->purchaseOrder),
            'name'                 => $this->name,
            'employees'            => EmployeeResource::collection($this->employees),
            'work_hour_started_at' => $this->work_hour_started_at,
            'work_hour_end_at'     => $this->work_hour_end_at,
            'started_at'           => isset($this->started_at) ? Carbon::parse($this->started_at)->format('Y-m-d') : null,
            'ended_at'             => isset($this->ended_at) ? Carbon::parse($this->ended_at)->format('Y-m-d') : null,
            'issued_at'            => isset($this->ended_at) ? Carbon::parse($this->ended_at)->format('Y-m-d') : null,
            'status'               => $this->status,
            'remark'               => $this->remark,
            'quotation_remark'     => $this->quotation_remark,
            'from'                 => $this->from,
            'address'              => $this->address,
            'person_in_charge'     => $this->person_in_charge,
            'issued_at'            => isset($this->issued_at) ? Carbon::parse($this->issued_at)->format('Y-m-d') : null,
        ];
    }
}
