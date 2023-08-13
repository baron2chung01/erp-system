<?php

namespace App\Models;

class Subcontractor extends BaseModel
{
    public $table = 'subcontractors';

    public $fillable = [
        'name',
        'email',
        'address',
        'phone',
        'contact_person',
        'fax',
        'website',
        'remark',
        'payment_term',
        'delivery_address',
        'status',
    ];

    protected $casts = [
        'name'             => 'string',
        'email'            => 'string',
        'address'          => 'string',
        'phone'            => 'string',
        'contact_person'   => 'string',
        'fax'              => 'string',
        'website'          => 'string',
        'payment_term'     => 'string',
        'delivery_address' => 'string',
        'status'           => 'integer',
    ];

    public static array $rules = [
        'name'             => 'required|string',
        'email'            => 'nullable|string',
        'address'          => 'nullable|string',
        'phone'            => 'required|string',
        'contact_person'   => 'nullable|string',
        'fax'              => 'nullable|string',
        'website'          => 'nullable|string',
        'remark'           => 'nullable|string',
        'payment_term'     => 'nullable|string',
        'delivery_address' => 'nullable|string',
        'status'           => 'required|integer',
    ];

    public function tasks()
    {
        return $this->hasMany(SubcontractorHasTask::class, 'subcontractor_id')->orderBy('id', 'ASC');
    }

}
