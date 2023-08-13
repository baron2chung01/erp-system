<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class OutlineAgreement
 * @package App\Models
 * @version November 6, 2022, 5:44 am UTC
 *
 * @property string $oa_number
 * @property string $expiry_date
 * @property string $issue_date
 * @property number $total_budgeted_manday
 * @property number $total_budgeted_third_party_cost
 * @property string $name
 * @property string $title
 * @property string $description
 * @property integer $status
 * @property string $created_by
 * @property string $updated_by
 * @property string $deleted_by
 */
class OutlineAgreement extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'outline_agreements';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'id',
        'location_id',
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
        'remark',
        'fax',
        'website',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'                              => 'integer',
        'location_id'                     => 'integer',
        'oa_number'                       => 'string',
        'expiry_date'                     => 'date',
        'issue_date'                      => 'date',
        'total_budgeted_manday'           => 'float',
        'total_budgeted_third_party_cost' => 'float',
        'name'                            => 'string',
        'title'                           => 'string',
        'description'                     => 'string',
        'status'                          => 'integer',
        'contract_started_at'             => 'datetime',
        'contract_ended_at'               => 'datetime',
        'contract_sum'                    => 'float',
        'standard_monthly_fee'            => 'float',
        'payment_term'                    => 'string',
        'created_by'                      => 'string',
        'updated_by'                      => 'string',
        'deleted_by'                      => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'location_id'                     => 'required|integer',
        'oa_number'                       => 'nullable|string|max:255',
        'expiry_date'                     => 'nullable',
        'issue_date'                      => 'nullable',
        'total_budgeted_manday'           => 'required|numeric',
        'total_budgeted_third_party_cost' => 'required|numeric',
        'name'                            => 'required|string|max:255',
        'title'                           => 'nullable|string|max:255',
        'description'                     => 'nullable|string',
        'status'                          => 'nullable|integer',
        'created_by'                      => 'nullable|string|max:36',
        'updated_by'                      => 'nullable|string|max:36',
        'deleted_by'                      => 'nullable|string|max:36',
        'created_at'                      => 'nullable',
        'updated_at'                      => 'nullable',
        'deleted_at'                      => 'nullable',
    ];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'outline_agreements_has_clients', 'outline_agreement_id', 'client_id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function oaHasGroupTasks()
    {
        // Minions TODO: Update to use belongsToMany or hasManyThrough
        // Minions TODO: Update relation to get OA's group task assigned tasks
        return $this->belongsToMany(GroupTask::class, 'outline_agreements_has_group_tasks', 'outline_agreement_id', 'group_task_id')->orderBy('created_at', 'ASC');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'outline_agreements_has_locations', 'outline_agreement_id', 'location_id');
    }

    public function displayName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->name . ' (' . $this->oa_number . ')',
        );
    }
}
