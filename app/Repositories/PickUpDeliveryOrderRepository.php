<?php

namespace App\Repositories;

use App\Models\PickUpDeliveryOrder;
use App\Models\ProductOrder;

/**
 * Class ProductOrderRepository
 * @package App\Repositories
 * @version August 31, 2019, 11:18 am UTC
 *
 * @method ProductOrder findWithoutFail($id, $columns = ['*'])
 * @method ProductOrder find($id, $columns = ['*'])
 * @method ProductOrder first($columns = ['*'])
*/
class PickUpDeliveryOrderRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'order_id',
        'price',
        'pick_up_delivery_order_request_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return PickUpDeliveryOrder::class;
    }
}
