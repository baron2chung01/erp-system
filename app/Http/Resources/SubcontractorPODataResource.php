<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubcontractorPODataResource extends JsonResource
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
            'exp_working_started_at' => $this->exp_working_started_at,
            'exp_working_ended_at'   => $this->exp_working_ended_at,
            'issued_at'              => $this->issued_at,
            'remark'                 => $this->remark,
            'delivery_mode'          => $this->shipping_address,
            'payment_term'           => $this->payment_term,
            'purchase_order_id'      => $this->purchase_order_id,
            'subcontractor_id'       => $this->subcontractor_id,
            'letterhead'             => $this->letterhead,
        ];
    }
}
