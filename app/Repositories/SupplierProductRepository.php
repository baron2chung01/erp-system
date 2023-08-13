<?php

namespace App\Repositories;

use App\Models\SupplierProduct;
use App\Repositories\BaseRepository;

class SupplierProductRepository extends BaseRepository
{
    protected $fieldSearchable = [
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

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return SupplierProduct::class;
    }
}
