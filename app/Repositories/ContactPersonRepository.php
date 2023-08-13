<?php

namespace App\Repositories;

use App\Models\ContactPerson;
use App\Repositories\BaseRepository;

class ContactPersonRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'contact_name',
        'phone',
        'general_line',
        'direct_line',
        'whatsapp',
        'fax',
        'status',
        'client_id',
        'address',
        'job_title',
        'email',
        'remark',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ContactPerson::class;
    }
}
