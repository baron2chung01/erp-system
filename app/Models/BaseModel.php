<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Project
 * @package App\Models
 * @version July 27, 2022, 1:13 pm UTC
 */
class BaseModel extends Model
{
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    const DISABLED = 0;
    const ACTIVE = 1;
    const STATUS = [
        self::DISABLED => 'Disabled',
        self::ACTIVE   => 'Active',
    ];
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            if (!isset($builder->getQuery()->orders)) {
                $builder->orderBy('updated_at', 'desc');
            }
        });
    }
}
