<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Location
 * @package App\Models
 * @version July 2, 2022, 7:20 am UTC
 *
 * @property integer $purchase_order_id
 * @property string $name
 * @property number $latitude
 * @property number $longitude
 * @property number $radius
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class Location extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'locations';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'location_name',
        'code',
        'address',
        'latitude',
        'longitude',
        'radius',
        'status',
        'person_in_charge',
        'work_hour_started_at',
        'work_hour_ended_at',
        'site_office_location',
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
        'id'                   => 'integer',
        'name'                 => 'string',
        'latitude'             => 'float',
        'longitude'            => 'float',
        'radius'               => 'float',
        'status'               => 'integer',
        'person_in_charge'     => 'string',
        'work_hour_started_at' => 'datetime',
        'work_hour_ended_at'   => 'datetime',
        'site_office_location' => 'string',
        'remark'               => 'string',
        'created_by'           => 'integer',
        'updated_by'           => 'integer',
        'deleted_by'           => 'integer',
    ];

    public function addresses()
    {
        return $this->hasMany(LocationAddress::class, 'location_id');
    }

}
