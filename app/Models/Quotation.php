<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Quotation
 * @package App\Models
 * @version July 2, 2022, 7:18 am UTC
 *
 * @property integer $client_id
 * @property string $name
 * @property string $description
 * @property string $email
 * @property string $address
 * @property string $phone
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class Quotation extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'quotations';

    protected $dates = ['deleted_at'];

    public $fillable = [
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
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'          => 'integer',
        'client_id'   => 'integer',
        'name'        => 'string',
        'description' => 'string',
        'email'       => 'string',
        'address'     => 'string',
        'phone'       => 'string',
        'status'      => 'integer',
        'created_by'  => 'integer',
        'updated_by'  => 'integer',
        'deleted_by'  => 'integer',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}