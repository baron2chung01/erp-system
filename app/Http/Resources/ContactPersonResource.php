<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactPersonResource extends JsonResource
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
            'contact_name' => $this->contact_name,
            'phone' => $this->phone,
            'general_line' => $this->general_line,
            'direct_line' => $this->direct_line,
            'whatsapp' => $this->whatsapp,
            'fax' => $this->fax,
            'status' => $this->status,
            'client_id' => $this->client_id,
            'address' => $this->address,
            'remark' => $this->remark,
        ];
    }
}
