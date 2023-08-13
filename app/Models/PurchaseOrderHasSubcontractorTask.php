<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderHasSubcontractorTask extends Model
{
    // TODO: TO BE DELETED: same as POHasTask after removal of subcon_id to separate table
    const DISABLED = 0;
    const ACTIVE = 1;
    const STATUS = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];

    public $table = 'purchase_orders_has_subcontractor_tasks';

    public $fillable = [
        'id',
        'purchase_order_id',
        'group_task_id',
        'subcontractor_id',
        'task_id',
        'group_task_name',
        'task_name',
        'task_no',
        'qty',
        'unit_price',
        'total_price',
        'unit',
        'status',
        'remark',
    ];

    protected $casts = [
        'group_task_name' => 'string',
        'task_name'       => 'string',
        'task_no'         => 'string',
        'qty'             => 'float',
        'unit_price'      => 'float',
        'total_price'     => 'float',
        'unit'            => 'string',
        'status'          => 'integer',
    ];

    public static array $rules = [
        'purchase_order_id' => 'required|integer',
        'group_task_id'     => 'required|integer',
        'subcontractor_id'  => 'nullable|integer',
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

    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function priceInfo()
    {
        return $this->hasMany(SubcontractorTaskPrice::class, 'pivot_id');
    }

}
