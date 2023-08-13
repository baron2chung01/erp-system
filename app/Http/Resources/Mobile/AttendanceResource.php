<?php

namespace App\Http\Resources\Mobile;

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
        return [
            'id'                  => $this->id,
            'work_order_id'       => $this->work_order_id,
            'purchase_order_name' => $this->workOrder->purchaseOrder->name ?? null,
            'location'            => $this->workOrder->location->location_name ?? null,
            'location_id'         => $this->workOrder->location->id ?? null,
            'in_range'            => $this->in_range,
            'type'                => Attendance::CHECK_TYPES[$this->type],
            'attendance_at'       => Carbon::parse($this->attendance_at)->setTimezone('Asia/Singapore')->format('Y-m-d H:i:s'),
            'lat'                 => $this->latitude,
            'lon'                 => $this->longitude,
        ];
    }
}
