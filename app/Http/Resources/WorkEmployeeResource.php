<?php

namespace App\Http\Resources;

use App\Models\WorkOrderEmployee;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkEmployeeResource extends JsonResource
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
            'id'       => $this->id,
            'employee' => EmployeeResource::make($this->employee),
            'leader'   => $this->leader,
            'status'   => $this->status
        ];
    }
}
