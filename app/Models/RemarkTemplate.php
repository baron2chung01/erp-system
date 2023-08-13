<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemarkTemplate extends Model
{

    const ACTIVE = 1;
    const INACTIVE = 0;
    const STATUS = [
        self::ACTIVE   => 'Active',
        self::INACTIVE => 'Inactive',
    ];

    const QUOTATION = 0;
    const SUPPLIER = 1;
    const CLIENT = 2;
    const PO = 3;
    const LOCATION = 4;
    const WO = 5;

    const TYPE = [
        self::QUOTATION => 'Quotation',
        self::SUPPLIER  => 'Supplier',
        self::CLIENT    => 'Client',
        self::PO        => 'PO',
        self::LOCATION  => 'Location',
        self::WO        => 'WO',
    ];

    public $table = 'remark_templates';

    public $fillable = [
        'remark',
        'type',
        'title',
        'status',
    ];

    protected $casts = [
        'type'   => 'integer',
        'title'  => 'string',
        'status' => 'integer',
    ];

    public static array $rules = [
        'remark' => 'required',
        'type'   => 'required',
        'title'  => 'required',
        'status' => 'required',
    ];

}
