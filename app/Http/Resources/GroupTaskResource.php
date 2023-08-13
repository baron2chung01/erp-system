<?php

namespace App\Http\Resources;

use App\Models\GroupTask;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupTaskResource extends JsonResource
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
            'key'          => $this->id,
            'display_name' => $this->display_name,
            'name'         => $this->group_task_name,
            'qty'          => $this->qty,
            'unit_price'   => $this->unit_price,
            'total_price'  => $this->total_price,
            'position'     => $this->pivot->position ?? null,
            'status'       => $this->status,
            'tasks'        => TaskResource::collection($this->tasks),
        ];
    }
}
