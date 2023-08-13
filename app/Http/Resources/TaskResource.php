<?php

namespace App\Http\Resources;

use App\Models\Task;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'name'         => $this->name,
            'display_name' => $this->display_name,
            'qty'          => $this->qty,
            'task_no'      => $this->task_no,
            'unit'         => $this->unit,
            'unit_price'   => $this->unit_price,
            'total_price'  => $this->total_price,
            'status'       => $this->status,
        ];
    }
}
