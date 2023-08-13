<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Ibeacon
 * @package App\Models
 * @version July 2, 2022, 7:21 am UTC
 *
 * @property integer $location_id
 * @property integer $major
 * @property integer $minor
 * @property string $location
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class Ibeacon extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'ibeacons';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'location_id',
        'major',
        'minor',
        'location',
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
        'id'           => 'integer',
        'location_id'  => 'integer',
        'major'        => 'integer',
        'minor'        => 'integer',
        'location'     => 'string',
        'status'       => 'integer',
        'created_by'   => 'integer',
        'updated_by'   => 'integer',
        'deleted_by'   => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'location_id'  => 'required',
        'major'        => 'required|integer',
        'minor'        => 'required|integer',
        'location'     => 'required|string|max:255',
        'status'       => 'required|integer',
        'created_by'   => 'nullable',
        'updated_by'   => 'nullable',
        'deleted_by'   => 'nullable',
        'created_at'   => 'nullable',
        'updated_at'   => 'nullable',
        'deleted_at'   => 'nullable',
    ];

}
