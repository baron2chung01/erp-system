<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Attendance
 * @package App\Models
 * @version July 2, 2022, 7:21 am UTC
 *
 * @property integer $employee_id
 * @property integer $location_id
 * @property string $project_code
 * @property integer $type
 * @property string|\Carbon\Carbon $attendance_at
 * @property number $latitude
 * @property number $longitude
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class Attendance extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    const CHECKIN = 0;
    const CHECKOUT = 1;
    const CHECK_TYPES = [
        self::CHECKIN  => 'Check In',
        self::CHECKOUT => 'Check Out',
    ];

    const ACTIVE = 1;
    const INACTIVE = 0;
    const STATUS = [
        self::ACTIVE   => 'Active',
        self::INACTIVE => 'Inactive',
    ];

    public $table = 'attendances';

    public $fillable = [
        'employee_id',
        'work_order_id',
        'type',
        'attendance_at',
        'latitude',
        'longitude',
        'in_range',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    protected $dates = ['deleted_at'];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'            => 'integer',
        'employee_id'   => 'integer',
        'work_order_id' => 'string',
        'type'          => 'integer',
        'attendance_at' => 'datetime',
        'latitude'      => 'float',
        'longitude'     => 'float',
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
        return $this->belongsTo(WorkOrder::class)->withTrashed();
    }
}
