<?php

namespace App\Http\Resources\Mobile;

use App\Models\SystemConfig;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemConfigResource extends JsonResource
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
            'id'      => $this->id,
            'content' => json_decode($this->content),
            'name'    => $this->name,
        ];
    }
}
