<?php

namespace App\Repositories;

use App\Models\OfficialReceipt;
use App\Repositories\BaseRepository;

/**
 * Class OfficialReceiptRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:23 am UTC
*/

class OfficialReceiptRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'invoice_id',
        'code',
        'amount',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
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
        return OfficialReceipt::class;
    }
}
