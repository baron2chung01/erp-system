<?php

namespace App\Repositories;

use App\Models\PurchaseOrderHasTask;
use App\Repositories\BaseRepository;

class PurchaseOrderHasTaskRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'purchase_order_id',
        'group_task_id',
        'group_task_name',
        'task_name',
        'task_id',
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
        return PurchaseOrderHasTask::class;
    }
}
