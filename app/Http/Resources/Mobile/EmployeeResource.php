<?php

namespace App\Http\Resources\Mobile;

use App\Http\Resources\RoleResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'id'           => $this->id,
            'first_name'   => $this->first_name,
            'last_name'    => $this->last_name,
            'chinese_name' => $this->chinese_name,
            'day_rate'     => $this->day_rate,
            'hour_rate'    => $this->hour_rate,
            'phone'        => $this->phone,
            'email'        => $this->email,
            'roles'        => RoleResource::collection($this->whenLoaded('roles')),
        ];
    }
}