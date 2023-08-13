<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class WorkOrderHasWorkInstruction
 * @package App\Models
 * @version January 7, 2023, 4:29 am UTC
 *
 * @property foreignId $work_order_id
 * @property foreignId $work_instruction_id
 * @property integer $status
 */
class WorkOrderHasWorkInstruction extends Model
{
    use SoftDeletes;

    use HasFactory;

    const DISABLED = 0;
    const ACTIVE   = 1;
    const STATUS   = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];

    public $table = 'work_orders_has_work_instructions';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'work_order_id',
        'work_instruction_id',
        'status',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'work_order_id'       => 'required|integer',
        'work_instruction_id' => 'required|integer',
        'status'              => 'required|integer',
    ];

}
