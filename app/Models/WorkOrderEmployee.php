<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class WorkOrderEmployee
 * @package App\Models
 * @version July 2, 2022, 7:25 am UTC
 *
 * @property integer $work_order_id
 * @property integer $employee_id
 * @property boolean $leader
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class WorkOrderEmployee extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    const DISABLED = 0;
    const ACTIVE   = 1;
    const STATUS   = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];

    public $table = 'work_orders_has_employees';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'work_order_id',
        'employee_id',
        'leader',
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
        'work_order_id' => 'integer',
        'employee_id'   => 'integer',
        'leader'        => 'boolean',
        'status'        => 'integer',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
        'deleted_by'    => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

}