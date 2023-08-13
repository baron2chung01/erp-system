<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Invoice
 * @package App\Models
 * @version July 2, 2022, 7:23 am UTC
 *
 * @property integer $purchase_order_id
 * @property string $code
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class Invoice extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'invoices';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'purchase_order_id',
        'code',
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
        'id'                => 'integer',
        'purchase_order_id' => 'integer',
        'code'              => 'string',
        'status'            => 'integer',
        'created_by'        => 'integer',
        'updated_by'        => 'integer',
        'deleted_by'        => 'integer',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function officialReceipt()
    {
        return $this->hasMany(OfficialReceipt::class);
    }

}
