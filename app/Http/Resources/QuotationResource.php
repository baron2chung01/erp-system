<?php

namespace App\Http\Resources;

use App\Models\Quotation;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
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
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'email'       => $this->email,
            'address'     => $this->address,
            'phone'       => $this->phone,
            'status'      => $this->status,
            'client'      => ClientResource::make($this->whenLoaded('client')),
        ];
    }
}