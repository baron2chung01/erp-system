<?php

namespace App\Http\Resources;

use App\Http\Resources\SelectImageResource;
use App\Models\WorkOrder;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderDetailResource extends JsonResource
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
            'id'                          => $this->id,
            'wo_number'                   => $this->wo_number,
            'name'                        => $this->name,
            'work_hour_started_at'        => $this->work_hour_started_at,
            'work_hour_ended_at'          => $this->work_hour_ended_at,
            'started_at'                  => $this->started_at,
            'ended_at'                    => $this->ended_at,
            'address'                     => $this->address,
            'purchase_order_id'           => $this->whenLoaded('purchaseOrder')->id ?? null,
            'group_task_id'               => $this->whenLoaded('groupTasks')->pluck('id'),
            'employees_id'                => $this->whenLoaded('employees')->pluck('id'),
            'work_instructions_id'        => $this->whenLoaded('workInstructions')->pluck('id'),
            'location_id'                 => $this->whenLoaded('location')->id,
            'status'                      => $this->status,
            'remark'                      => $this->remark,
            'quotation_remark'            => $this->quotation_remark,
            'images'                      => SelectImageResource::collection($this->resultImages),
            'signatures'                  => AssetResource::collection($this->signatureImages),
            'result_image_pdfs'           => AssetResource::collection($this->resultImagePdfs),
            'service_report_pdfs'         => AssetResource::collection($this->serviceReportPdfs),
            'completion_summary_pdfs'     => AssetResource::collection($this->completionSummaryPdfs),
            'signed_completion_summaries' => AssetResource::collection($this->signedCompletionSummaries),
            'from'                        => $this->from,
            'person_in_charge'            => $this->person_in_charge,
            'issued_at'                   => $this->issued_at,
            'task_info'                   => $this->relationLoaded('taskInfo') ? $this->taskInfo->groupBy('group_task_id') : $this->whenLoaded('taskInfo'),

        ];
    }
}
