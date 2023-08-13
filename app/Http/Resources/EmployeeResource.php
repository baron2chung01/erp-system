<?php

namespace App\Http\Resources;

use App\Models\Asset;
use App\Models\Employee;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $assets = Asset::where('related_id', $this->id)->where('related_type', Employee::class)->get();

        return [
            'id'           => $this->id,
            'employee_no'  => $this->employee_no,
            'first_name'   => $this->first_name,
            'last_name'    => $this->last_name,
            'chinese_name' => $this->chinese_name,
            'full_name'    => $this->first_name . ' ' . $this->last_name,
            'day_rate'     => $this->day_rate,
            'hour_rate'    => $this->hour_rate,
            'phone'        => $this->phone,
            'email'        => $this->email,
            'status'       => $this->status,
            'remark'       => $this->remark,
            'attendances'  => AttendanceResource::collection($this->whenLoaded('attendances')),
            'roles'        => RoleResource::collection($this->whenLoaded('roles')),
            'work_orders'  => WorkOrderResource::collection($this->whenLoaded('workOrders')),
            'assets'       => AssetResource::collection($assets),
            'role'         => count($this->roles) ? $this->roles[0]->role_name : null,
        ];
    }
}
