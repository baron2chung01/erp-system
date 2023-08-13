<?php

namespace App\Repositories;

use App\Models\PurchaseOrderHasSupplier;
use App\Repositories\BaseRepository;

class PurchaseOrderHasSupplierRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'purchase_order_id',
        'supplier_id',
        // 'product_id',
        // 'product_name',
        // 'product_desc',
        // 'qty',
        // 'unit_price',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return PurchaseOrderHasSupplier::class;
    }
}
