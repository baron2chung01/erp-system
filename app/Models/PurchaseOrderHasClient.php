<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderHasClient extends Model
{
    use HasFactory;

    const DISABLED = 0;
    const ACTIVE = 1;
    const STATUS = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];

    public $table = 'purchase_orders_has_clients';

    public $fillable = [
        'purchase_order_id',
        'client_id',
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
        'purchase_order_id' => 'required|integer',
        'client_id'         => 'required|integer',
        'status'            => 'required|integer',
    ];

}
