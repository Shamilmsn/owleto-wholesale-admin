<?php

namespace App\Repositories;

use App\Models\SubscriptionPackage;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class CartRepository
 * @package App\Repositories
 * @version September 4, 2019, 3:38 pm UTC
 *
 * @method SubscriptionPackage findWithoutFail($id, $columns = ['*'])
 * @method SubscriptionPackage find($id, $columns = ['*'])
 * @method SubscriptionPackage first($columns = ['*'])
*/
class SubscriptionPackageRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'product_id',
        'user_id',
        'quantity',
        'delivery_time',
        'days'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return SubscriptionPackage::class;
    }
}
