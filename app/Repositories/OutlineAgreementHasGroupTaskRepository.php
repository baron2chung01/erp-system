<?php

namespace App\Repositories;

use App\Models\OutlineAgreementHasGroupTask;
use App\Repositories\BaseRepository;

/**
 * Class OutlineAgreementHasGroupTaskRepository
 * @package App\Repositories
 * @version December 16, 2022, 9:00 am UTC
 */
class OutlineAgreementHasGroupTaskRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'outline_agreement_id',
        'group_task_id',
        'status',
        'actual_qty',
        'actual_price',
        'started_at',
        'ended_at',
        'deleted_at'
    ];

    /*
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
        return OutlineAgreementHasGroupTask::class;
    }
}
