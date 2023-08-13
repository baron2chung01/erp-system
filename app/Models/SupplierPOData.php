<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPOData extends Model
{
    const COMPANY_A = 0;
    const COMPANY_B = 1;
    const COMPANY_C = 2;
    const LHEAD = [
        self::COMPANY_A => 'COMPANY_A',
        self::COMPANY_B => 'COMPANY_B',
        self::COMPANY_C => 'COMPANY_C',
    ];

    public $table = 'supplier_p_o_datas';

    public $fillable = [
        'issued_at',
        'remark',
        'shipping_address',
        'payment_term',
        'purchase_order_id',
        'supplier_id',
        'letterhead',
        'exp_working_started_at',
        'exp_working_ended_at',
    ];

    protected $casts = [
        'issued_at'              => 'datetime',
        'shipping_address'       => 'string',
        'payment_term'           => 'string',
        'exp_working_started_at' => 'datetime',
        'exp_working_ended_at'   => 'datetime',
    ];

    public static array $rules = [
        'issued_at'              => 'nullable',
        'remark'                 => 'nullable',
        'shipping_address'       => 'nullable',
        'payment_term'           => 'nullable',
        'purchase_order_id'      => 'required|integer',
        'supplier_id'            => 'required|integer',
        'letterhead'             => 'nullable',
        'exp_working_started_at' => 'nullable',
        'exp_working_ended_at'   => 'nullable',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

}
