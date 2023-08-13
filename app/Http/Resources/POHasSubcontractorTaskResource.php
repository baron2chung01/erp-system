<?php

namespace App\Http\Resources;

use App\Models\PurchaseOrderHasSubcontractorTask;
use App\Models\SubcontractorHasTask;
use Illuminate\Http\Resources\Json\JsonResource;

class POHasSubcontractorTaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'key'               => $this->id,
            'purchase_order_id' => $this->purchase_order_id,
            'group_task_id'     => $this->group_task_id,
            'subcontractor_id'  => $this->subcontractor_id,
            'task_id'           => $this->task_id,
            'group_task_name'   => $this->group_task_name,
            'task_name'         => $this->task_name,
            'task_no'           => $this->task_no,
            'qty'               => $this->qty,
            'unit_price'        => $this->unit_price,
            'total_price'       => $this->total_price,
            'unit'              => $this->unit,
            'status'            => $this->status,
            'remark'            => $this->remark,
            'price_info'        => SubcontractorTaskPriceResource::collection($this->priceInfo),
        ];
    }
}
