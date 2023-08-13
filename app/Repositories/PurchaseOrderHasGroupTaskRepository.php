<?php

namespace App\Repositories;

use App\Models\PurchaseOrderHasGroupTask;
use App\Repositories\BaseRepository;

/**
 * Class PurchaseOrderHasGroupTaskRepository
 * @package App\Repositories
 * @version December 16, 2022, 9:06 am UTC
 */

class PurchaseOrderHasGroupTaskRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'purchase_order_id',
        'group_task_id',
        'status',
        'actual_qty',
        'actual_price',
        'position',
        'started_at',
        'ended_at',
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
        return PurchaseOrderHasGroupTask::class;
    }
}
