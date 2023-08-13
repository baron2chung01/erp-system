<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPOHasTask extends Model
{
    const DISABLED = 0;
    const ACTIVE = 1;
    const STATUS = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];

    public $table = 'supplier_p_o_has_tasks';

    public $fillable = [
        'purchase_order_id',
        'supplier_po_id',
        'group_task_id',
        'group_task_name',
        'task_name',
        'task_id',
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
        'supplier_po_id'    => 'required|integer',
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

    public function supplierPO()
    {
        return $this->belongsTo(SupplierPOData::class, 'supplier_po_id');
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
