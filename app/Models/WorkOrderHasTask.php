<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderHasTask extends Model
{
    const DISABLED = 0;
    const ACTIVE = 1;
    const STATUS = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];

    public $table = 'work_orders_has_tasks';

    public $fillable = [
        'work_order_id',
        'group_task_id',
        'group_task_name',
        'task_id',
        'task_name',
        'task_no',
        'qty',
        'unit',
        'unit_price',
        'total_price',
        'status',
    ];

    protected $casts = [
        'group_task_name' => 'string',
        'task_name'       => 'string',
        'task_no'         => 'string',
        'qty'             => 'float',
        'unit'            => 'string',
        'unit_price'      => 'float',
        'total_price'     => 'float',
        'status'          => 'integer',
    ];

    public static array $rules = [
        'work_order_id'   => 'required|integer',
        'group_task_id'   => 'required|integer',
        'group_task_name' => 'required|string',
        'task_id'         => 'required|integer',
        'task_name'       => 'required|string',
        'task_no'         => 'nullable|string',
        'qty'             => 'required|float',
        'unit'            => 'required|string',
        'unit_price'      => 'required|float',
        'total_price'     => 'required|float',
        'status'          => 'required|integer',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function groupTask()
    {
        return $this->belongsTo(GroupTask::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

}
