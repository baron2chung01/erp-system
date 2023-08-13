<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierProductResource extends JsonResource
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
            'id'          => $this->id,
            'key'         => $this->id,
            'supplier_id' => $this->supplier_id,
            'desc'        => $this->desc,
            'name'        => $this->name,
            'remark'      => $this->remark,
            'qty'         => $this->qty,
            'unit'        => $this->unit,
            'unit_price'  => $this->unit_price,
            'total_price' => $this->total_price,
            'product_no'  => $this->product_no,
            'status'      => $this->status,
        ];
    }
}
