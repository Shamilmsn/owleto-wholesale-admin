<?php

namespace App\Repositories;

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\OptionGroup;
use App\Models\ProductAttributeOption;

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
class ProductAttributeOptionRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'product_id',
        'attribute_id',
        'attribute_option_id',
        'base_product_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return ProductAttributeOption::class;
    }
}
