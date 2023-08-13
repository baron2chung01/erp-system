<?php

namespace App\Http\Resources;

use App\Models\Ibeacon;
use Illuminate\Http\Resources\Json\JsonResource;

class IbeaconResource extends JsonResource
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
            'id'        => $this->id,
            'name'      => $this->name,
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,
            'radius'    => $this->radius,
            'status'    => $this->status
        ];
    }
}
