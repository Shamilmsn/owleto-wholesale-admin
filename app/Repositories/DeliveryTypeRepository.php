<?php

namespace App\Repositories;

use App\Models\DeliveryType;
use App\Models\Field;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class FieldRepository
 * @package App\Repositories
 * @version April 11, 2020, 1:57 pm UTC
 *
 * @method Field findWithoutFail($id, $columns = ['*'])
 * @method Field find($id, $columns = ['*'])
 * @method Field first($columns = ['*'])
*/
class DeliveryTypeRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'charge'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return DeliveryType::class;
    }
}
