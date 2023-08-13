<?php

namespace App\Models;

class SupplierProduct extends BaseModel
{
    public $table = 'supplier_products';

    public $fillable = [
        'id',
        'supplier_id',
        'desc',
        'name',
        'remark',
        'qty',
        'unit',
        'unit_price',
        'total_price',
        'product_no',
        'status',
    ];

    protected $casts = [
        'supplier_id' => 'integer',
        'desc'        => 'string',
        'name'        => 'string',
        'remark'      => 'string',
        'qty'         => 'float',
        'unit'        => 'string',
        'unit_price'  => 'float',
        'total_price' => 'float',
        'product_no'  => 'float',
        'status'      => 'integer',
    ];

    public static array $rules = [
        'supplier_id' => 'required|integer',
        'desc'        => 'required|string',
        'name'        => 'required|string',
        'remark'      => 'nullable|string',
        'qty'         => 'nullable',
        'unit'        => 'required|string',
        'unit_price'  => 'nullable',
        'total_price' => 'nullable',
        'product_no'  => 'nullable',
        'status'      => 'required',
    ];

}
