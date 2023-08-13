<?php

namespace App\Http\Resources;

use App\Models\Location;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            'id'                   => $this->id,
            'location_name'        => $this->location_name,
            'code'                 => $this->code,
            'lat'                  => $this->latitude,
            'lon'                  => $this->longitude,
            'radius'               => $this->radius,
            'status'               => $this->status,
            'person_in_charge'     => $this->person_in_charge,
            'work_hour_started_at' => $this->work_hour_started_at,
            'work_hour_ended_at'   => $this->work_hour_ended_at,
            'site_office_location' => $this->site_office_location,
            'remark'               => $this->remark,
            'address'              => $this->address,
        ];
    }
}
