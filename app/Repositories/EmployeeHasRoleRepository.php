<?php

namespace App\Repositories;

use App\Models\EmployeeHasRole;
use App\Repositories\BaseRepository;
use Illuminate\Support\Str;

/**
 * Class EmployeeHasRoleRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:22 am UTC
 */

class EmployeeHasRoleRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'role_id',
        'employee_id',
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
        return EmployeeHasRole::class;
    }

    public function getUuidField()
    {

    }
}