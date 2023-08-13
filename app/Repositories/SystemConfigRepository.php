<?php

namespace App\Repositories;

use App\Models\SystemConfig;
use App\Repositories\BaseRepository;

/**
 * Class SystemConfigRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:27 am UTC
*/

class SystemConfigRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'code',
        'name',
        'content',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
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
        return SystemConfig::class;
    }
}
