<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubcontractorTaskPrice extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $table = 'subcontractor_task_prices';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'id',
        'pivot_id',
        'subcontractor_id',
        'purchase_order_id',
        'task_id',
        'subcontractor_name',
        'qty',
        'actual_qty',
        'payment_qty',
        'unit_price',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'                 => 'integer',
        'subcontractor_id'   => 'integer',
        'purchase_order_id'  => 'integer',
        'task_id'            => 'integer',
        'subcontractor_name' => 'string',
        'qty'                => 'float',
        'actual_qty'         => 'float',
        'payment_qty'        => 'float',
        'unit_price'         => 'float',
    ];

    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function pivot()
    {
        return $this->belongsTo(PurchaseOrderHasSubcontractorTask::class, 'pivot_id');
    }
}
