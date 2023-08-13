<?php

namespace App\Http\Resources;

use App\Models\SubcontractorHasTask;
use Illuminate\Http\Resources\Json\JsonResource;

class SubcontractorTaskPriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $disabled = !SubcontractorHasTask::where('subcontractor_id', $this->subcontractor_id)->where('task_id', $this->task_id)->exists();
        return [
            'id'                 => $this->id,
            'key'                => $this->id,
            'pivot_id'           => $this->pivot_id,
            'subcontractor_id'   => $this->subcontractor_id,
            'purchase_order_id'  => $this->purchase_order_id,
            'task_id'            => $this->task_id,
            'subcontractor_name' => $this->subcontractor_name,
            'qty'                => $this->qty,
            'actual_qty'         => $this->actual_qty,
            'payment_qty'        => $this->payment_qty,
            'unit_price'         => $this->unit_price,
            'disabled'           => $disabled,
        ];
    }
}
