<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Role
 * @package App\Models
 * @version August 9, 2022, 12:51 pm UTC
 *
 * @property string $name
 * @property string $code
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class Role extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    const ACTIVE   = 1;
    const DISABLED = 0;

    const STATUS = [
        self::ACTIVE   => 'Active',
        self::DISABLED => 'Disabled',
    ];

    public $table = 'role';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'id',
        'role_name',
        'code',
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
        'id'         => 'integer',
        'name'       => 'string',
        'code'       => 'string',
        'status'     => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
    ];

}