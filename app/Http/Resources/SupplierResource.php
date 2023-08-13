<?php

namespace App\Http\Resources;

use App\Models\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'id'             => $this->id,
            'name'           => $this->name,
            'email'          => $this->email,
            'address'        => $this->address,
            'phone'          => $this->phone,
            'status'         => $this->status,
            'contact_person' => $this->contact_person,
            'payment_term'   => $this->payment_term,
            'delivery_mode'  => $this->delivery_mode,
            'remark'         => $this->remark,
            'fax'            => $this->fax,
            'website'        => $this->website,
            // 'tasks'          => $this->tasks,
            'products'       => SupplierProductResource::collection($this->products),

        ];
    }
}
