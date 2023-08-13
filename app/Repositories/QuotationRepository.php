<?php

namespace App\Repositories;

use App\Models\Quotation;
use App\Repositories\BaseRepository;

/**
 * Class QuotationRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:18 am UTC
 */

class QuotationRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'client_id',
        'name',
        'description',
        'email',
        'address',
        'phone',
        'status',
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
        return Quotation::class;
    }

    public function getUuidField()
    {
        // TODO: Implement getUuidField() method.
    }

}