<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Task
 * @package App\Models
 * @version August 9, 2022, 12:15 pm UTC
 *
 * @property integer $group_task_id
 * @property string $name
 * @property number $qty
 * @property number $unit_price
 * @property number $total_price
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class Task extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'tasks';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'id',
        'group_task_id',
        'name',
        'qty',
        'task_no',
        'unit',
        'unit_price',
        'total_price',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'            => 'integer',
        'group_task_id' => 'integer',
        'name'          => 'string',
        'qty'           => 'float',
        'task_no'       => 'string',
        'unit'          => 'string',
        'unit_price'    => 'float',
        'total_price'   => 'float',
        'status'        => 'integer',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
        'deleted_by'    => 'integer',
    ];

    public function groupTask()
    {
        return $this->belongsTo(GroupTask::class);
    }

    public function outlineAgreement()
    {
        return $this->belongsToMany(OutlineAgreement::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsToMany(PurchaseOrder::class);
    }

    public function workOrder()
    {
        return $this->belongsToMany(WorkOrder::class);
    }

    public function displayName(): Attribute
    {
        return Attribute::make(
            get: fn() => isset($this->task_no) ? $this->name . ' (Task No.: ' . $this->task_no . ')'
            : $this->name . ' (Task No.: N/A)',
        );
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'suppliers_has_tasks', 'task_id', 'supplier_id');
    }

}
