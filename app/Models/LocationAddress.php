<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationAddress extends Model
{
    const DISABLED    = 0;
    const ACTIVE = 1;

    const STATUS     = [
        self::DISABLED    => 'Disabled',
        self::ACTIVE => 'Active',
    ];

    public $table = 'location_addresses';

    public $fillable = [
        'id',
        'location_id',
        'address',
        'remark',
        'status'
    ];

    protected $casts = [
        'status' => 'integer'
    ];

    public static array $rules = [
        'location_id' => 'required|integer',
        'address' => 'required|string',
        'remark' => 'nullable',
        'status' => 'required|integer'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }


}
