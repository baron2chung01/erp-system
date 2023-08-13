<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $workOrder = WorkOrder::find($this->work_order_id);
        return [
            'id'            => $this->id,
            'location'      => $this->workOrder->location->location_name ?? null,
            'location_id'   => $this->workOrder->location->id ?? null,
            'in_range'      => $this->in_range,
            'type'          => $this->type,
            'display_type'  => Attendance::CHECK_TYPES[$this->type],
            'attendance_at' => Carbon::parse($this->attendance_at)->format('Y-m-d H:i:s'),
            'lat'           => $this->latitude,
            'lon'           => $this->longitude,
            'employee'      => EmployeeResource::make($this->whenLoaded('employee')),
        ];
    }
}
