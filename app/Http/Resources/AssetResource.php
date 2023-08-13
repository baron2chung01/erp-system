<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
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
            'uid'        => -$this->id,
            'url'        => asset($this->url),
            'name'       => $this->file_name ?? str_replace('/application/public/', '', $this->resource_path),
            'status'     => $this->status,
            'created_at' => Carbon::parse($this->created_at),
        ];
    }
}
