<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class OfficialReceipt
 * @package App\Models
 * @version July 2, 2022, 7:23 am UTC
 *
 * @property integer $invoice_id
 * @property string $code
 * @property number $amount
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class OfficialReceipt extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'official_receipts';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'invoice_id',
        'code',
        'amount',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'                    => 'integer',
        'invoice_id'            => 'integer',
        'code'                  => 'string',
        'amount'                => 'float',
        'status'                => 'integer',
        'created_by'            => 'integer',
        'updated_by'            => 'integer',
        'deleted_by'            => 'integer'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

}
