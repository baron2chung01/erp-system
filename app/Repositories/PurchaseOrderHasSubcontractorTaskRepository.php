<?php

namespace App\Repositories;

use App\Models\PurchaseOrderHasSubcontractorTask;
use App\Repositories\BaseRepository;

class PurchaseOrderHasSubcontractorTaskRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'purchase_order_id',
        'group_task_id',
        'group_task_name',
        'task_name',
        'task_id',
        'task_no',
        'qty',
        'unit_price',
        'total_price',
        'unit',
        'status',
        'remark',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return PurchaseOrderHasSubcontractorTask::class;
    }
}
