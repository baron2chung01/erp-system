<?php

namespace App\Repositories;

use App\Models\Location;
use App\Repositories\BaseRepository;

/**
 * Class LocationRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:20 am UTC
 */

class LocationRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'purchase_order_id',
        'name',
        'code',
        'address',
        'latitude',
        'longitude',
        'radius',
        'status',
        'person_in_charge',
        'work_hour_started_at',
        'work_hour_ended_at',
        'site_office_location',
        'remark',
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
        return Location::class;
    }
}
