<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class POSupplierProductResource extends JsonResource
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
            'id'           => $this->pivot->id,
            'key'          => $this->pivot->id,
            'supplier_id'  => $this->supplier_id,
            'product_id'   => $this->pivot->product_id,
            'product_name' => $this->pivot->name,
            'desc'         => $this->pivot->desc,
            'qty'          => $this->pivot->qty,
            'unit'         => $this->pivot->unit,
            'unit_price'   => $this->pivot->unit_price,
            'total_price'  => $this->pivot->total_price,
            'product_no'   => $this->pivot->product_no,
            'status'       => $this->pivot->status,
            'remark'       => $this->remark,
        ];
    }
}
