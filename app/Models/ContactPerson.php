<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactPerson extends Model
{
    const DISABLED = 0;
    const ACTIVE = 1;

    const STATUS = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];

    public $table = 'contact_people';

    public $fillable = [
        'contact_name',
        'phone',
        'general_line',
        'direct_line',
        'whatsapp',
        'fax',
        'status',
        'client_id',
        'address',
        'job_title',
        'email',
        'remark',
    ];

    protected $casts = [
        'contact_name' => 'string',
        'phone'        => 'string',
        'general_line' => 'string',
        'direct_line'  => 'string',
        'whatsapp'     => 'string',
        'fax'          => 'string',
        'status'       => 'integer',
    ];

    public static array $rules = [
        'contact_name' => 'required|string',
        'phone'        => 'nullable',
        'general_line' => 'nullable',
        'direct_line'  => 'nullable',
        'whatsapp'     => 'nullable',
        'fax'          => 'nullable',
        'status'       => 'required',
        'client_id'    => 'required|integer',
        'address'      => 'nullable',
        'job_title'    => 'nullable',
        'email'        => 'nullable',
        'remark'       => 'nullable',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

}
