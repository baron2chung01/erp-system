<?php

namespace App\Repositories;

use App\Models\OutlineAgreementHasLocation;
use App\Repositories\BaseRepository;

class OutlineAgreementHasLocationRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'outline_agreement_id',
        'location_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return OutlineAgreementHasLocation::class;
    }
}
