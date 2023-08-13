<?php

namespace App\Http\Resources;

use App\Models\WorkInstruction;
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
        return [
            'id'                  => $this->id,
            'work_instruction_no' => $this->work_instruction_no,
            'name'                => $this->name,
            'content'             => $this->content,
            'remarks'             => $this->remarks,
            'status'              => $this->status,
            'instruction_doc'     => AssetResource::collection($this->instructionDoc),
        ];
    }
}
