<?php

namespace App\Repositories;

use App\Models\Client;
use App\Repositories\BaseRepository;
use Illuminate\Support\Str;

/**
 * Class ClientRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:17 am UTC
 */

class ClientRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'code',
        'name',
        'address',
        'fax',
        'phone',
        'contact_name',
        'email',
        'status',
        'general_line',
        'direct_line',
        'whatsapp',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Client::class;
    }

    public function getUuidField()
    {

    }
}