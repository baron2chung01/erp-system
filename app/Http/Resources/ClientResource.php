<?php

namespace App\Http\Resources;

use App\Models\Client;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'id'                => $this->id,
            'code'              => $this->code,
            'name'              => $this->name,
            'address'           => $this->address,
            'general_line'      => $this->general_line,
            'direct_line'       => $this->direct_line,
            'whatsapp'          => $this->whatsapp,
            'fax'               => $this->fax,
            'phone'             => $this->phone,
            'contact_name'      => $this->contact_name,
            'email'             => $this->email,
            'status'            => $this->status,
            'contact_people'    => $this->contactPeople,
            'contact_people_id' => isset($this->contactPeople) ? $this->contactPeople->pluck('id') : null,
        ];
    }
}
