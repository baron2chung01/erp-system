<?php

namespace App\Http\Resources;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'id'                 => $this->id,
            'code'               => $this->code,
            'name'               => $this->name,
            'description'        => $this->description,
            'expected_start_at'  => Carbon::parse($this->expected_start_at)->format('Y-m-d H:i:s'),
            'expected_end_at'    => Carbon::parse($this->expected_end_at)->format('Y-m-d H:i:s'),
            'actual_start_at'    => Carbon::parse($this->actual_start_at)->format('Y-m-d H:i:s'),
            'actual_end_at'      => Carbon::parse($this->actual_end_at)->format('Y-m-d H:i:s'),
            'maintenance_period' => $this->maintenance_period,
            'expected_cost'      => $this->expected_cost,
            'actual_cost'        => $this->actual_cost,
            'status'             => $this->status,
            'locations'          => LocationResource::collection($this->whenLoaded('locations')),
            'instructions'       => WorkInstructionResource::collection($this->whenLoaded('instructions')),
            'groupTasks'         => GroupTaskResource::collection($this->whenLoaded('groupTasks')),
            'workOrders'         => WorkOrderResource::collection($this->whenLoaded('workOrders')),
        ];
    }
}