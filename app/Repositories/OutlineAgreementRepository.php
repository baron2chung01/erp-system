<?php

namespace App\Repositories;

use App\Models\OutlineAgreement;
use App\Repositories\BaseRepository;

/**
 * Class OutlineAgreementRepository
 * @package App\Repositories
 * @version November 6, 2022, 6:24 am UTC
 */
class OutlineAgreementRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'clients_id',
        'oa_number',
        'expiry_date',
        'issue_date',
        'total_budgeted_manday',
        'total_budgeted_third_party_cost',
        'name',
        'title',
        'description',
        'status',
        'contract_started_at',
        'contract_ended_at',
        'contract_sum',
        'standard_monthly_fee',
        'payment_term',
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
        return OutlineAgreement::class;
    }

    public function getUuidField()
    {
        // TODO: Implement getUuidField() method.
    }
}