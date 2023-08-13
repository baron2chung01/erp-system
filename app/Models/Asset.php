<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Project
 * @package App\Models
 * @version July 27, 2022, 1:13 pm UTC
 */
class Asset extends BaseModel
{
    const SIGNED   = 2;
    const UNSIGNED = 1;
    const INACTIVE = 0;
    const STATUS   = [
        self::SIGNED   => 'Signed',
        self::UNSIGNED => 'Unsigned',
        self::INACTIVE => 'Inactive',
    ];

    public $table    = 'assets';
    public $fillable = [
        'related_id',
        'asset_type',
        'related_type',
        'url',
        'resource_path',
        'file_name',
        'file_size',
        'status',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    protected $dates = ['deleted_at'];
}
