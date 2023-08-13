<?php

namespace App\Repositories;

use App\Models\Ibeacon;
use App\Repositories\BaseRepository;

/**
 * Class IbeaconRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:21 am UTC
 */

class IbeaconRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'location_id',
        'major',
        'minor',
        'location',
        'status',
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
        return Ibeacon::class;
    }
}
