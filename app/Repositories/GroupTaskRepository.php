<?php

namespace App\Repositories;

use App\Models\GroupTask;
use App\Repositories\BaseRepository;

/**
 * Class GroupTaskRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:19 am UTC
 */

class GroupTaskRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'purchase_order_id',
        'name',
        'qty',
        'unit_price',
        'total_price',
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
        return GroupTask::class;
    }

    public function getUuidField()
    {
        return "";
    }
}
