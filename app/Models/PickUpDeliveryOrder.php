<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickUpDeliveryOrder extends Model
{
    use HasFactory;

    public $table = 'pick_up_delivery_orders';


    public $fillable = [
        'order_id',
        'price',
        'pick_up_delivery_order_request_id',
    ];

    public function pickUpDeliveryOrderRequest()
    {
        return $this->belongsTo(PickUpDeliveryOrderRequest::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

}
