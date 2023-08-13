<?php

namespace App\Repositories;

use App\Models\WorkOrderEmployee;
use App\Repositories\BaseRepository;

/**
 * Class WorkOrderEmployeeRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:25 am UTC
 */

class WorkOrderEmployeeRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'work_order_id',
        'employee_id',
        'leader',
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
        return WorkOrderEmployee::class;
    }

    public function getUuidField()
    {
    }
}