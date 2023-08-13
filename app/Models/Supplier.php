<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Supplier
 * @package App\Models
 * @version July 2, 2022, 7:17 am UTC
 *
 * @property string $name
 * @property string $email
 * @property string $address
 * @property string $phone
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class Supplier extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    const DISABLED = 0;
    const ACTIVE = 1;
    const STATUS = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];

    public $table = 'suppliers';

    protected $dates = ['deleted_at'];

    public $fillable = [
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
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'             => 'integer',
        'name'           => 'string',
        'email'          => 'string',
        'address'        => 'string',
        'phone'          => 'string',
        'status'         => 'integer',
        'contact_person' => 'string',
        'payment_term'   => 'string',
        'delivery_mode'  => 'string',
        'remark'         => 'string',
        'fax'            => 'string',
        'website'        => 'string',
        'created_by'     => 'integer',
        'updated_by'     => 'integer',
        'deleted_by'     => 'integer',
    ];

    public function products()
    {
        return $this->hasMany(SupplierProduct::class, 'supplier_id')->orderBy('id');
    }
}
