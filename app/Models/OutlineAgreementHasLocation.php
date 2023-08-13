<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutlineAgreementHasLocation extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $table = 'outline_agreements_has_locations';

    public $fillable = [
        'outline_agreement_id',
        'location_id',
    ];

    protected $casts = [

    ];

    public static array $rules = [
        'outline_agreement_id' => 'required|integer',
        'location_id'          => 'required|integer',
    ];

}
