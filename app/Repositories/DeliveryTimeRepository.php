<?php

namespace App\Repositories;

use App\Models\DeliveryTime;
use App\Models\SubscriptionPackage;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class CartRepository
 * @package App\Repositories
 * @version September 4, 2019, 3:38 pm UTC
 *
 * @method DeliveryTime findWithoutFail($id, $columns = ['*'])
 * @method DeliveryTime find($id, $columns = ['*'])
 * @method DeliveryTime first($columns = ['*'])
*/
class DeliveryTimeRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return DeliveryTime::class;
    }
}
