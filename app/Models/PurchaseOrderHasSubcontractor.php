<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderHasSubcontractor extends Model
{
    public $table = 'purchase_orders_has_subcontractors';

    public $fillable = [
        'purchase_order_id',
        'subcontractor_id',
    ];

    protected $casts = [

    ];

    public static array $rules = [
        'purchase_order_id' => 'required|integer',
        'subcontractor_id'  => 'required|integer',

    ];

}
