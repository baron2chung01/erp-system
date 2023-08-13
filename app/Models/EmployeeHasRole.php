<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EmployeeRole
 * @package App\Models
 * @version July 2, 2022, 7:22 am UTC
 *
 * @property integer $role_id
 * @property integer $employee_id
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class EmployeeHasRole extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    const DISABLED = 0;
    const ACTIVE   = 1;
    const STATUS   = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];

    public $table = 'employees_has_roles';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'role_id',
        'employee_id',
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
        'id'          => 'integer',
        'role_id'     => 'integer',
        'employee_id' => 'integer',
        'status'      => 'integer',
        'created_by'  => 'integer',
        'updated_by'  => 'integer',
        'deleted_by'  => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'role_id'     => 'required',
        'employee_id' => 'required',
        'status'      => 'required|integer',
        'created_by'  => 'nullable',
        'updated_by'  => 'nullable',
        'deleted_by'  => 'nullable',
        'created_at'  => 'nullable',
        'updated_at'  => 'nullable',
        'deleted_at'  => 'nullable',
    ];

}