<?php

namespace App\Repositories;

use App\Models\Area;
use App\Models\Attribute;
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
class AreaRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'city_id',
        'covered_kilometer',
        'address',
        'updated_at'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Area::class;
    }
}
