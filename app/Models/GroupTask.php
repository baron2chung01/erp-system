<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class GroupTask
 * @package App\Models
 * @version July 2, 2022, 7:19 am UTC
 *
 * @property integer $purchase_order_id
 * @property string $name
 * @property number $qty
 * @property number $unit_price
 * @property number $total_price
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class GroupTask extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'group_tasks';

    protected $dates = ['deleted_at'];

    const TAKEN = 2;
    const ACTIVE = 1;
    const INACTIVE = 0;
    const STATUS = [
        self::ACTIVE   => 'Active',
        self::INACTIVE => 'Inactive',
        self::TAKEN    => 'Taken',
    ];

    public $fillable = [
        'id',
        'group_task_name',
        'qty',
        'unit_price',
        'total_price',
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
        'id'              => 'integer',
        'group_task_name' => 'string',
        'qty'             => 'float',
        'unit_price'      => 'float',
        'total_price'     => 'float',
        'status'          => 'integer',
        'created_by'      => 'integer',
        'updated_by'      => 'integer',
        'deleted_by'      => 'integer',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('id', 'asc');
    }

    public function oas()
    {
        return $this->belongsToMany(OutlineAgreement::class, 'outline_agreements_has_group_tasks', 'group_task_id', 'outline_agreement_id');
    }

    public function pos()
    {
        return $this->belongsToMany(PurchaseOrder::class, 'purchase_orders_has_group_tasks', 'group_task_id', 'purchase_order_id');
    }

    public function displayName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->group_task_name . ' (ID: ' . $this->id . ')',
        );
    }

}
