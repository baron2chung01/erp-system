<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class OutlineAgreementHasGroupTask
 * @package App\Models
 * @version December 16, 2022, 9:00 am UTC
 *
 * @property foreignId $outline_agreement_id
 * @property foreignId $task_id
 * @property integer $status
 * @property integer $actual_qty
 * @property number $actual_price
 * @property string $started_at
 * @property string $ended_at
 * @property string $deleted_at
 */
class OutlineAgreementHasGroupTask extends Model
{
    use SoftDeletes;

    use HasFactory;

    const DISABLED = 0;
    const ACTIVE = 1;
    const STATUS = [
        self::DISABLED => 'Disabled',
        self::ACTIVE => 'Active',
    ];

    public $table = 'outline_agreements_has_group_tasks';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'outline_agreement_id',
        'group_task_id',
        'status',
        'actual_qty',
        'actual_price',
        'started_at',
        'ended_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'status'       => 'integer',
        'actual_qty'   => 'float',
        'actual_price' => 'float',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'outline_agreement_id' => 'required|integer',
        'task_id'              => 'required|integer',
        'status'               => 'required|integer',
        'actual_qty'           => 'required|integer',
        'actual_price'         => 'required|numeric',
        'started_at'           => 'required',
        'ended_at'             => 'required',
        'deleted_at'           => 'nullable',
    ];

}
