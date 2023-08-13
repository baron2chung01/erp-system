<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubcontractorHasTask extends Model
{
    const DISABLED = 0;
    const ACTIVE = 1;
    const STATUS = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];

    public $table = 'subcontractors_has_tasks';

    public $fillable = [
        'subcontractor_id',
        'outline_agreement_id',
        'group_task_id',
        'group_task_name',
        'task_id',
        'task_name',
        'task_no',
        'qty',
        'unit',
        'unit_price',
        'total_price',
        'remark',
        'status',
    ];

    protected $casts = [
        'outline_agreement_id' => 'string',
        'qty'                  => 'float',
        'unit_price'           => 'float',
        'status'               => 'integer',
    ];

    public static array $rules = [
        'subcontractor_id'     => 'required|integer',
        'outline_agreement_id' => 'required|integer',
        'group_task_id'        => 'nullable|integer',
        'group_task_name'      => 'nullable|string',
        'task_name'            => 'required|string',
        'task_id'              => 'required|integer',
        'task_no'              => 'nullable|string',
        'qty'                  => 'nullable',
        'unit_price'           => 'nullable',
        'total_price'          => 'nullable',
        'unit'                 => 'nullable',
        'remark'               => 'nullable',
        'status'               => 'required|integer',
    ];

    public function outlineAgreement()
    {
        return $this->belongsTo(OutlineAgreement::class);
    }

    public function subcontractor()
    {
        return $this->belongsTo(Supplier::class, 'subcontractor_id');
    }

    public function groupTask()
    {
        return $this->belongsTo(GroupTask::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

}
