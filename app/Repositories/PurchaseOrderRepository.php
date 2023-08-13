<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;
use App\Repositories\BaseRepository;

/**
 * Class PurchaseOrderRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:19 am UTC
 */

class PurchaseOrderRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'quotation_id',
        'po_number',
        'name',
        'description',
        'shipping_address',
        'contact_name',
        'status',
        'quotation_remark',
        'revise_count',
        'issued_at',
        'received_at',
        'date_to_AC_dept',
        'qty',
        'expect_delivered_at',
        'expect_completed_at',
        'actual_completed_at',
        'person_in_charge',
        'invoice_date',
        'invoice_no',
        'quot_ref',
        'quot_date',
        'quot_letterhead',
        'discount_type',
        'discount_value',
        'total_price',
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
        return PurchaseOrder::class;
    }

    public function getUuidField()
    {
    }
}
