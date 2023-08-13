<?php

namespace App\Repositories;

use App\Models\Template;
use App\Repositories\BaseRepository;

/**
 * Class TemplateRepository
 * @package App\Repositories
 * @version July 2, 2022, 7:26 am UTC
*/

class TemplateRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'code',
        'name',
        'content',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
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
        return Template::class;
    }
}
