<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SelectImageResource extends JsonResource
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
            'id'         => $this->id,
            'src'        => asset($this->url),
            'title'      => $this->id . ". " . str_replace('/work-order-result-image/', "", $this->url),
            'alt'        => "",
            'created_at' => $this->created_at,
        ];
    }
}
