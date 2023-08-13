<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderHasContactPerson extends Model
{
    const DISABLED = 0;
    const ACTIVE   = 1;
    const STATUS   = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];


    public $table = 'purchase_orders_has_contact_people';

    public $fillable = [
        'purchase_order_id',
        'contact_person_id',
        'status'
    ];

    protected $casts = [
        'status' => 'integer'
    ];

    public static array $rules = [
        'purchase_order_id' => 'required|integer',
        'contact_person_id' => 'required|integer',
        'status' => 'required|integer'
    ];


}
