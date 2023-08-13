<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Repositories\BaseRepository;
use Illuminate\Support\Str;

/**
 * Class EmployeeRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:18 am UTC
 */
class EmployeeRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'first_name',
        'last_name',
        'chinese_name',
        'day_rate',
        'hour_rate',
        'phone',
        'email',
        'status',
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
        return Employee::class;
    }

    public function getUuidField()
    {

    }
}