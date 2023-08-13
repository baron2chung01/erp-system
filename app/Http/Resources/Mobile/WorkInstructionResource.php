<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkInstructionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // if (isset($this->instructionDoc) && count($this->instructionDoc)) {
        //     $doc = $this->instructionDoc()->get();
        // } else {
        //     $doc = null;
        // }
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'content' => $this->content,
            'pdf'     => AssetResource::collection($this->instructionDoc()->get()),
        ];
    }
}