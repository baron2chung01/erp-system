<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductPriceHistory extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    public $table = 'product_price_histories';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'id',
        'purchase_order_id',
        'product_id',
        'name',
        'desc',
        'unit',
        'unit_price',
        'qty',
        'total_price',
        'product_no',
        'status',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'                => 'integer',
        'purchase_order_id' => 'integer',
        'product_id'        => 'integer',
        'name'              => 'string',
        'desc'              => 'string',
        'unit'              => 'string',
        'unit_price'        => 'float',
        'qty'               => 'float',
        'total_price'       => 'float',
        'product_no'        => 'string',
        'status'            => 'integer',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(SupplierProduct::class, 'product_id');
    }
}
