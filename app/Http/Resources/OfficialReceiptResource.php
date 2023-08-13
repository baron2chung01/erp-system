<?php

namespace App\Http\Resources;

use App\Models\OfficialReceipt;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficialReceiptResource extends JsonResource
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
            'id'     => $this->id,
            'code'   => $this->code,
            'amount' => $this->amount,
            'status' => $this->status
        ];
    }
}
