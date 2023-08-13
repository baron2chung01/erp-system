<?php

namespace App\Models;

use App\Http\Resources\SubcontractorTaskPriceResource;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderHasSupplier extends Model
{
    public $table = 'purchase_orders_has_suppliers';

    public $fillable = [
        'purchase_order_id',
        'supplier_id',
    ];

    protected $casts = [

    ];

    public static array $rules = [
        'purchase_order_id' => 'required|integer',
        'supplier_id'       => 'required|integer',

    ];

}
