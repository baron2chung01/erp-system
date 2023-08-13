<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PurchaseOrderHasGroupTask
 * @package App\Models
 * @version December 16, 2022, 9:06 am UTC
 *
 * @property foreignId $purchase_order_id
 * @property foreignId $task_id
 * @property integer $status
 * @property integer $actual_qty
 * @property number $actual_price
 * @property string $started_at
 * @property string $ended_at
 */
class PurchaseOrderHasGroupTask extends Model
{
    use SoftDeletes;

    use HasFactory;

    const DISABLED = 0;
    const ACTIVE = 1;
    const STATUS = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];

    public $table = 'purchase_orders_has_group_tasks';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'purchase_order_id',
        'group_task_id',
        'status',
        'actual_qty',
        'actual_price',
        'position',
        'started_at',
        'ended_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'status'       => 'integer',
        'actual_qty'   => 'integer',
        'actual_price' => 'float',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'purchase_order_id' => 'required|integer',
        'task_id'           => 'required|integer',
        'status'            => 'required|integer',
        'actual_qty'        => 'required|integer',
        'actual_price'      => 'required|numeric',
        'position'          => 'nullable|integer',
        'started_at'        => 'required',
        'ended_at'          => 'required',
    ];

}
