<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class ProductOrder
 * @package App\Models
 * @version August 31, 2019, 11:18 am UTC
 *
 * @property \App\Models\Product product
 * @property \App\Models\Option[] options
 * @property \App\Models\Order order
 * @property double price
 * @property integer quantity
 * @property integer product_id
 * @property integer order_id
 */
class ProductOrderRequestOrder extends Model
{

    public $table = 'product_order_request_orders';

    public $fillable = [
        'temporary_order_request_id',
        'price',
        'order_id',
    ];

    public function temporaryOrderRequest()
    {
        return $this->belongsTo(TemporaryOrderRequest::class, 'temporary_order_request_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

}
