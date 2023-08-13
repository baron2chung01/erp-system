<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class POHasTaskResource extends JsonResource
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
            'group_task_name'   => $this->group_task_name,
            'task_id'           => $this->task_id,
            'task_name'         => $this->task_name,
            'task_no'           => $this->task_no,
            'qty'               => $this->qty,
            'unit'              => $this->unit,
            'unit_price'        => $this->unit_price,
            'total_price'       => $this->total_price,
            'remark'            => $this->remark,
            'status'            => $this->status,
        ];
    }
}
