<?php

namespace App\Repositories;

use App\Models\WorkInstruction;
use App\Repositories\BaseRepository;

/**
 * Class WorkInstructionRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:24 am UTC
 */

class WorkInstructionRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'purchase_order_id',
        'name',
        'content',
        'status',
        'work_instruction_no',
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
        return WorkInstruction::class;
    }
}