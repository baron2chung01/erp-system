<?php

namespace App\Http\Resources\Mobile;

use App\Http\Resources\GroupTaskResource;
use App\Http\Resources\LocationResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\GroupTask;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        // getting the task data stored in WO level, but grouping them same format as GroupTaskResource

        // Group the tasks by their group task ID
        $groupedTasks = $this->taskInfo->groupBy('group_task_id');

        // Create a new collection for the reformatted task info
        $reformattedTasks = collect([]);

        // Iterate over the grouped tasks
        foreach ($groupedTasks as $groupId => $tasks) {
            // Get the group task from the original data
            $groupTask = GroupTask::find($groupId);

            // Create a new object for the reformatted group task
            $reformattedGroupTask = [
                'id'           => $groupTask['id'],
                'key'          => $groupTask['id'],
                'display_name' => $groupTask['display_name'],
                'name'         => $groupTask['group_task_name'],
                'qty'          => $groupTask['qty'],
                'unit_price'   => $groupTask['unit_price'],
                'total_price'  => $groupTask['total_price'],
                'position'     => $groupTask['position'],
                'status'       => $groupTask['status'],
                'tasks'        => [],
            ];

            // Iterate over the tasks in the group and add them to the reformatted group task
            foreach ($tasks as $task) {
                $reformattedGroupTask['tasks'][] = [
                    'id'           => $task['task_id'],
                    'name'         => $task['task_name'],
                    'display_name' => $task['task_name'] . ' (Task No.: ' . $task['task_no'] . ')',
                    'qty'          => $task['qty'],
                    'task_no'      => $task['task_no'],
                    'unit'         => $task['unit'],
                    'unit_price'   => $task['unit_price'],
                    'total_price'  => $task['total_price'],
                    'status'       => $task['status'],
                ];
            }

            // Add the reformatted group task to the new collection
            $reformattedTasks->push($reformattedGroupTask);
        }

        return [
            'id'                   => $this->id,
            'wi_number'            => $this->wo_number,
            'po_number'            => $this->purchaseOrder->po_number ?? null,
            'group_tasks'          => $reformattedTasks,
            'employees'            => EmployeeResource::collection($this->employees),
            'instructions'         => WorkInstructionResource::collection($this->workInstructions),
            'purchase_orders'      => new PurchaseOrderResource($this->purchaseOrder),
            'locations'            => new LocationResource($this->location),
            'work_hour_started_at' => Carbon::parse($this->work_hour_started_at)->format('H:i:s'),
            'work_hour_ended_at'   => Carbon::parse($this->work_hour_ended_at)->format('H:i:s'),
            'started_at'           => Carbon::parse($this->started_at)->format('Y-m-d'),
            'ended_at'             => Carbon::parse($this->ended_at)->format('Y-m-d'),
            'remark'               => $this->remark,
            'wi_remark'            => $this->quotation_remark,
            'address'              => $this->address,
            'images'               => AssetResource::collection($this->resultImages),
            'signatures'           => AssetResource::collection($this->signatureImages),
            'status'               => $this->status,
        ];
    }
}
