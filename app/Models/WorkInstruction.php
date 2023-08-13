<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class WorkInstruction
 * @package App\Models
 * @version July 2, 2022, 7:24 am UTC
 *
 * @property integer $purchase_order_id
 * @property string $name
 * @property string $content
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class WorkInstruction extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    const INSTRUCTION = 0;
    const ASSET       = [
        self::INSTRUCTION => 'instruction-document',
    ];

    public $table = 'work_instructions';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'name',
        'content',
        'remarks',
        'status',
        'work_instruction_no',
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
        'id'                  => 'integer',
        'work_order_id'       => 'integer',
        'name'                => 'string',
        'content'             => 'string',
        'remarks'             => 'string',
        'status'              => 'integer',
        'work_instruction_no' => 'string',
        'created_by'          => 'integer',
        'updated_by'          => 'integer',
        'deleted_by'          => 'integer',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function instructionDoc()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::INSTRUCTION);
    }

    public function displayName(): Attribute
    {
        return Attribute::make(
            get:fn() => $this->name . ' (' .$this->work_instruction_no. ')',
        );
    }

}
