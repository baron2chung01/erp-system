<?php

namespace App\Repositories;

use App\Models\Supplier;
use App\Repositories\BaseRepository;

/**
 * Class SupplierRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:17 am UTC
 */

class SupplierRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'email',
        'address',
        'phone',
        'status',
        'contact_person',
        'payment_term',
        'delivery_mode',
        'remark',
        'fax',
        'website',
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
        return Supplier::class;
    }
}
