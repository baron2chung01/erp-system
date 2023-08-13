<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderHasGroupTask extends Model
{
    const DISABLED = 0;
    const ACTIVE   = 1;
    const STATUS   = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];

    public $table = 'work_orders_has_group_tasks';

    public $fillable = [
        'work_order_id',
        'group_task_id',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public static array $rules = [
        'work_order_id' => 'required|integer',
        'group_task_id' => 'required|integer',
        'status'        => 'required|integer',
    ];

}
