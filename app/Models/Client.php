<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Client
 * @package App\Models
 * @version July 2, 2022, 7:17 am UTC
 *
 * @property string $code
 * @property string $name
 * @property string $address
 * @property string $fax
 * @property string $phone
 * @property string $contact_name
 * @property string $email
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class Client extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'clients';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'code',
        'name',
        'address',
        'fax',
        'phone',
        'contact_name',
        'email',
        'status',
        'general_line',
        'direct_line',
        'whatsapp',
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
        'id'           => 'integer',
        'code'         => 'string',
        'name'         => 'string',
        'address'      => 'string',
        'fax'          => 'string',
        'phone'        => 'string',
        'contact_name' => 'string',
        'email'        => 'string',
        'status'       => 'integer',
        'general_line' => 'string',
        'direct_line'  => 'string',
        'whatsapp'     => 'string',
        'created_by'   => 'integer',
        'updated_by'   => 'integer',
        'deleted_by'   => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'code'         => 'required|string|max:255',
        'name'         => 'required|string|max:255',
        'address'      => 'nullable|string',
        'fax'          => 'nullable|string|max:255',
        'phone'        => 'nullable|string|max:255',
        'contact_name' => 'nullable|string|max:255',
        'email'        => 'nullable|string|max:255',
    ];

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function outlineAgreements()
    {
        return $this->hasMany(OutlineAgreement::class);
    }

    public function contactPeople()
    {
        return $this->hasMany(ContactPerson::class, 'client_id');
    }

    public function displayName(): Attribute
    {
        return Attribute::make(
            get:fn() => $this->name . ' (' . $this->code . ')',
        );
    }

}
