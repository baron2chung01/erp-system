<?php

namespace App\Repositories;

use App\Models\PurchaseOrderHasContactPerson;
use App\Repositories\BaseRepository;

class PurchaseOrderHasContactPersonRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'purchase_order_id',
        'contact_person_id',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return PurchaseOrderHasContactPerson::class;
    }
}
