<?php

namespace App\Repositories;

use App\Models\ProductOrder;
use App\Models\ProductOrderRequestOrder;

//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class ProductOrderRepository
 * @package App\Repositories
 * @version August 31, 2019, 11:18 am UTC
 *
 * @method ProductOrder findWithoutFail($id, $columns = ['*'])
 * @method ProductOrder find($id, $columns = ['*'])
 * @method ProductOrder first($columns = ['*'])
*/
class ProductOrderRequestOrderRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'price',
        'temporary_order_request_id',
        'order_id',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return ProductOrderRequestOrder::class;
    }
}
