<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageOrder extends Model
{
    use HasFactory;

    public $table = 'package_orders';

    protected $casts = [
        'is_driver_approved' => 'boolean'
    ];

    public $fillable = [
        'order_id',
        'user_id',
        'order_status_id',
        'market_id',
        'delivery_address_id',
        'payment_method_id',
        'distance',
        'tax',
        'quantity',
        'package_id',
        'package_price',
        'price_per_delivery',
        'commission_percentage',
        'commission_amount',
        'day_id',
        'delivery_time_id',
        'market_balance',
        'date',
        'created_at',
        'canceled',
        'sector_id',
        'is_driver_approved',
        'latitude',
        'longitude',
        'address_data'
    ];

    const MORNING_TIME_LIMIT = 7;
    const EVENING_TIME_LIMIT = 15;

    public function getPricePerDeliveryAttribute($value)
    {
        return round($value,2);
    }

    public function getDriverCommissionAmountAttribute($value)
    {
        return round($value,2);
    }


    public function package()
    {
        return $this->belongsTo(SubscriptionPackage::class, 'package_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function orderDriver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function deliveryAddress()
    {
        return $this->belongsTo(DeliveryAddress::class, 'delivery_address_id', 'id');
    }

    public function market()
    {
        return $this->belongsTo(Market::class, 'market_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Kolkata')->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Kolkata')->format('d-m-Y H:i:s');
    }
}
