<?php

namespace App\Repositories;

use App\Models\WorkOrderHasWorkInstruction;
use App\Repositories\BaseRepository;

/**
 * Class WorkOrderHasWorkInstructionRepository
 * @package App\Repositories
 * @version January 7, 2023, 4:29 am UTC
*/

class WorkOrderHasWorkInstructionRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'work_order_id',
        'work_instruction_id',
        'status'
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
        return WorkOrderHasWorkInstruction::class;
    }
}
