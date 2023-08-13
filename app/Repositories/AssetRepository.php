<?php

namespace App\Repositories;

use App\Models\Asset;
use App\Repositories\BaseRepository;
use App\Traits\Arr;
use Illuminate\Support\Str;

/**
 * Class AssetRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:24 am UTC
 */
class AssetRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'related_id',
        'asset_type',
        'related_type',
        'url',
        'resource_path',
        'file_size',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Asset::class;
    }
}