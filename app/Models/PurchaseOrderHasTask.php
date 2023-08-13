<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderHasTask extends Model
{
    const DISABLED = 0;
    const ACTIVE = 1;
    const STATUS = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];

    public $table = 'purchase_orders_has_tasks';

    public $fillable = [
        'id',
        'purchase_order_id',
        'group_task_id',
        'group_task_name',
        'task_id',
        'task_name',
        'task_no',
        'qty',
        'unit',
        'unit_price',
        'total_price',
        'remark',
        'status',
    ];

    protected $casts = [
        'qty'        => 'float',
        'unit_price' => 'float',
        'status'     => 'integer',
    ];

    public static array $rules = [
        'purchase_order_id' => 'required|integer',
        'group_task_id'     => 'required|integer',
        'group_task_name'   => 'required|string',
        'task_name'         => 'required|string',
        'task_id'           => 'required|integer',
        'task_no'           => 'nullable|string',
        'qty'               => 'nullable',
        'unit_price'        => 'nullable',
        'total_price'       => 'nullable',
        'unit'              => 'nullable',
        'remark'            => 'nullable',
        'status'            => 'required|integer',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
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
