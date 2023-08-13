<?php

namespace App\Repositories;

use App\Models\RemarkTemplate;
use App\Repositories\BaseRepository;

class RemarkTemplateRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'remark',
        'type',
        'title',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return RemarkTemplate::class;
    }
}
