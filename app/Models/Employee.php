<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * Class Employee
 * @package App\Models
 * @version July 2, 2022, 7:18 am UTC
 *
 * @property string $firstname
 * @property string $lastname
 * @property string $chinese_name
 * @property number $day_rate
 * @property number $hour_rate
 * @property string $phone
 * @property string $email
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class Employee extends Authenticatable
{
//    use SoftDeletes;

    use HasFactory, HasApiTokens;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DISABLED = 0;
    const ACTIVE = 1;
    const STATUS = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];
    const LEADER = 1;
    const WORKER = 2;
    const SUBCONTRACTOR = 3;
    const ROLE = [
        self::LEADER        => 'Leader',
        self::WORKER        => 'Worker',
        self::SUBCONTRACTOR => 'Subcontractor',
    ];

    const IMAGE = 0;
    const ASSET = [
        self::IMAGE => 'image',
    ];

    public $table = 'employees';
//    protected $dates = ['deleted_at'];

    public $fillable = [
        'id',
        'first_name',
        'last_name',
        'employee_no',
        'chinese_name', // used as nickname
        'day_rate',
        'hour_rate',
        'phone',
        'email',
        'password',
        'is_subcon',
        'status',
        'remark',
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
        'id'           => 'integer',
        'first_name'   => 'string',
        'last_name'    => 'string',
        'chinese_name' => 'string',
        'day_rate'     => 'float',
        'hour_rate'    => 'float',
        'phone'        => 'string',
        'email'        => 'string',
        'is_subcon'    => 'boolean',
        'status'       => 'integer',
        'remark'       => 'string',
        'created_by'   => 'integer',
        'updated_by'   => 'integer',
        'deleted_by'   => 'integer',
    ];

    public function findForPassport($username)
    {
        return $this->where('email', $username)->first();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function workOrders()
    {
        return $this->belongsToMany(WorkOrder::class, 'work_orders_has_employees');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'employees_has_roles', 'employee_id', 'role_id');
    }

    public function displayName(): Attribute
    {
        if ($this->roles()->first() != null) {
            $result = $this->first_name . ' ' . $this->last_name . ' (' . $this->roles()->first()->role_name . ')';
        } else {
            $result = $this->first_name . ' ' . $this->last_name . ' (Role not set)';
        }
        return Attribute::make(
            get: fn() => $result,
        );
    }
}
