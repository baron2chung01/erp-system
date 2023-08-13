<?php

namespace App\Http\Resources;

use App\Http\Resources\GroupTaskResource;
use App\Http\Resources\LocationDataResource;
use App\Models\Client;
use App\Models\OutlineAgreement;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OutlineAgreementResource extends JsonResource
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
            'id'                              => $this->id,
            'locations_id'                    => isset($this->locations) ? $this->locations->pluck('id') : null,
            'location_data'                   => LocationDataResource::collection($this->locations),
            'oa_number'                       => $this->oa_number,
            'expiry_date'                     => isset($this->expiry_date) ? Carbon::parse($this->expiry_date)->format('Y-m-d') : null,
            'issue_date'                      => isset($this->issue_date) ? Carbon::parse($this->issue_date)->format('Y-m-d') : null,
            'total_budgeted_manday'           => $this->total_budgeted_manday,
            'total_budgeted_third_party_cost' => $this->total_budgeted_third_party_cost,
            'name'                            => $this->name,
            'title'                           => $this->title,
            'description'                     => $this->description,
            'status'                          => $this->status,
            'group_tasks'                     => GroupTaskResource::collection($this->whenLoaded('oaHasGroupTasks')),
            'clients'                         => NameListResource::collection($this->whenLoaded('clients')),
            'clients_id'                      => $this->clients->pluck('id'),
            'client_name'                     => count($this->clients) ? $this->clients[0]->name : null,
            'contract_started_at'             => $this->contract_started_at,
            'contract_ended_at'               => $this->contract_ended_at,
            'contract_sum'                    => $this->contract_sum,
            'standard_monthly_fee'            => $this->standard_monthly_fee,
        ];
    }
}
