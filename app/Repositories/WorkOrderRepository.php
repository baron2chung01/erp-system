<?php

namespace App\Repositories;

use App\Models\Asset;
use App\Models\WorkOrder;
use App\Repositories\BaseRepository;
use App\Traits\Arr;
use Illuminate\Support\Str;

/**
 * Class WorkOrderRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:24 am UTC
 */
class WorkOrderRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'purchase_order_id',
        'wo_number',
        'work_instruction_id',
        'location_id',
        'work_hour_started_at',
        'work_hour_ended_at',
        'started_at',
        'ended_at',
        'remark',
        'quotation_remark',
        'status',
        'from',
        'person_in_charge',
        'issued_at',
        'address',
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
        return WorkOrder::class;
    }
}
