<?php

namespace App\Repositories;

use App\Models\SupplierPOData;
use App\Repositories\BaseRepository;

class SupplierPODataRepository extends BaseRepository
{
    protected $fieldSearchable = [
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

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return SupplierPOData::class;
    }
}
