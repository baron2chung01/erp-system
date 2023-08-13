<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Template
 * @package App\Models
 * @version July 2, 2022, 7:26 am UTC
 *
 * @property string $code
 * @property string $name
 * @property string $content
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class Template extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'templates';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'code',
        'name',
        'content',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'            => 'integer',
        'code'          => 'string',
        'name'          => 'string',
        'content'       => 'string',
        'status'        => 'integer',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
        'deleted_by'    => 'integer'
    ];
}
