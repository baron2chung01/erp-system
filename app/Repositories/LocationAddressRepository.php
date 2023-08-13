<?php

namespace App\Repositories;

use App\Models\LocationAddress;
use App\Repositories\BaseRepository;

class LocationAddressRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'location_id',
        'address',
        'remark',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return LocationAddress::class;
    }
}
