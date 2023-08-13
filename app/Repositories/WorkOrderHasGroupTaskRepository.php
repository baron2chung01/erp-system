<?php

namespace App\Repositories;

use App\Models\WorkOrderHasGroupTask;
use App\Repositories\BaseRepository;

class WorkOrderHasGroupTaskRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'work_order_id',
        'group_task_id',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return WorkOrderHasGroupTask::class;
    }
}
