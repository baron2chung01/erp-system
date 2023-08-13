<?php

namespace App\Repositories;

use App\Models\WorkOrderHasTask;
use App\Repositories\BaseRepository;

class WorkOrderHasTaskRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'work_order_id',
        'group_task_id',
        'group_task_name',
        'task_id',
        'task_name',
        'task_no',
        'qty',
        'unit',
        'unit_price',
        'total_price',
        'status',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return WorkOrderHasTask::class;
    }
}
