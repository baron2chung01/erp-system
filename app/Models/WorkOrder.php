<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class WorkOrder
 * @package App\Models
 * @version July 2, 2022, 7:24 am UTC
 *
 * @property integer $purchase_order_id
 * @property string|\Carbon\Carbon $work_at
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class WorkOrder extends Model
{
    use SoftDeletes;
    use HasFactory;
    use LogsActivity;

    const TBS = 0;
    const SCHEDULED = 1;
    const COMPLETED = 2;
    const CANCELLED = 3;
    const STATUS = [
        self::TBS       => 'To Be Scheduled',
        self::SCHEDULED => 'Scheduled',
        self::COMPLETED => 'Completed',
        self::CANCELLED => 'Cancelled',
    ];

    const MOBILE_STATUS = [
        'tbs'       => self::TBS,
        'scheduled' => self::SCHEDULED,
        'completed' => self::COMPLETED,
        'cancelled' => self::CANCELLED,
    ];

    const RESULT_IMAGE = 0;
    const SIGNATURE_IMAGE = 1;
    const RESULT_IMAGE_PDF = 2;
    const SERVICE_REPORT_PDF = 3;
    const COMPLETION_SUMMARY_PDF = 4;
    const ASSET = [
        self::RESULT_IMAGE           => 'result-image',
        self::SIGNATURE_IMAGE        => 'signature-image',
        self::RESULT_IMAGE_PDF       => 'result-image-pdf',
        self::SERVICE_REPORT_PDF     => 'service-report-pdf',
        self::COMPLETION_SUMMARY_PDF => 'completion-summary-pdf',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'purchase_order_id'   => 'required',
        'work_instruction_id' => 'required',
        'location_id'         => 'required',
        'started_at'          => 'required',
        'ended_at'            => 'required',
        'status'              => 'required',
    ];
    public $table = 'work_orders';
    public $fillable = [
        'id',
        'name',
        'wo_number',
        'purchase_order_id',
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
    protected $dates = ['deleted_at'];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'                   => 'integer',
        'work_instruction_id'  => 'integer',
        'purchase_order_id'    => 'integer',
        'location_id'          => 'integer',
        'work_hour_started_at' => 'datetime',
        'work_hour_ended_at'   => 'datetime',
        'started_at'           => 'datetime',
        'ended_at'             => 'datetime',
        'remark'               => 'string',
        'quotation_remark'     => 'string',
        'status'               => 'integer',
        'from'                 => 'string',
        'person_in_charge'     => 'string',
        'issued_at'            => 'datetime',
        'address'              => 'string',
        'created_by'           => 'integer',
        'updated_by'           => 'integer',
        'deleted_by'           => 'integer',
    ];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'work_orders_has_employees');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function groupTasks()
    {
        return $this->belongsToMany(GroupTask::class, 'work_orders_has_group_tasks', 'work_order_id', 'group_task_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function resultImages()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::RESULT_IMAGE)
            ->orderBy('created_at', 'asc');
    }

    public function signatureImages()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::SIGNATURE_IMAGE);
    }

    public function activeSignatureImages()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::SIGNATURE_IMAGE)->where('status', '!=', Asset::INACTIVE);
    }

    // public function woHasGroupTasks()
    // {
    //     // Minions TODO: Update to use belongsToMany or hasManyThrough
    //     // Minions TODO: Update relation to get WO's group task assigned tasks
    //     return $this->hasMany(GroupTask::class);
    // }

    public function workInstructions()
    {
        return $this->belongsToMany(WorkInstruction::class, 'work_orders_has_work_instructions');
    }

    public function resultImagePdfs()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::RESULT_IMAGE_PDF);
    }

    public function serviceReportPdfs()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::SERVICE_REPORT_PDF);
    }

    public function completionSummaryPdfs()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::COMPLETION_SUMMARY_PDF);
    }

    public function signedCompletionSummaries()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::COMPLETION_SUMMARY_PDF)->where('status', ASSET::SIGNED);
    }

    public function addresses()
    {
        return $this->belongsToMany(LocationAddress::class, 'work_orders_has_addresses', 'work_order_id', 'address_id');
    }

    public function taskInfo()
    {
        return $this->hasMany(WorkOrderHasTask::class, 'work_order_id', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
        // Chain fluent methods for configuration options
    }

}
