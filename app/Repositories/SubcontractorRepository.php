<?php

namespace App\Repositories;

use App\Models\Subcontractor;
use App\Repositories\BaseRepository;

class SubcontractorRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'email',
        'address',
        'phone',
        'contact_person',
        'fax',
        'website',
        'remark',
        'payment_term',
        'delivery_address',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Subcontractor::class;
    }
}
