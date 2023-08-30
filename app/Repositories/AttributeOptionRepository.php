<?php

namespace App\Repositories;

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\OptionGroup;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class OptionGroupRepository
 * @package App\Repositories
 * @version April 6, 2020, 10:47 am UTC
 *
 * @method OptionGroup findWithoutFail($id, $columns = ['*'])
 * @method OptionGroup find($id, $columns = ['*'])
 * @method OptionGroup first($columns = ['*'])
*/
class AttributeOptionRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'attribute_id',
        'meta'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return AttributeOption::class;
    }
}
