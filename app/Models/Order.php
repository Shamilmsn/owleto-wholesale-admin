<?php
/**
 * File name: Order.php
 * Last modified: 2020.04.30 at 08:21:08
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Models;

use App\Mail\OrderDeliveredMail;
use App\Notifications\DriverAssignedNotification;
use App\Notifications\DriverAssignedNotificationToUser;
use App\Notifications\OrderDeliveredPushNotifictaion;
use Carbon\Carbon;
use Eloquent as Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/**
 * Class Order
 * @package App\Models
 * @version August 31, 2019, 11:11 am UTC
 *
 * @property \App\Models\User user
 * @property DeliveryAddress deliveryAddress
 * @property \App\Models\Payment payment
 * @property \App\Models\OrderStatus orderStatus
 * @property \App\Models\ProductOrder[] productOrders
 * @property integer user_id
 * @property integer order_status_id
 * @property integer payment_id
 * @property double tax
 * @property double delivery_fee
 * @property string id
 * @property int delivery_address_id
 * @property string hint
 */
class Order extends Model
{

    public $table = 'orders';
    public $fillable = [
        'user_id',
        'order_status_id',
        'tax',
        'hint',
        'payment_id',
        'delivery_address_id',
        'delivery_fee',
        'active',
        'driver_id',
        'owleto_commission_amount',
        'total_amount',
        'market_balance',
        'market_id',
        'payment_method_id',
        'payment_gateway',
        'razorpay_order_id',
        'total_amount',
        'sub_total',
        'delivery_type_id',
        'payment_method_id',
        'type',
        'amount_from_wallet',
        'is_wallet_used',
        'distance',
        'is_canceled',
        'sector_id',
        'is_order_approved',
        'is_driver_approved',
        'latitude',
        'longitude',
        'address_data',
        'order_category',
        'parent_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'order_status_id' => 'integer',
        'tax' => 'double',
        'hint' => 'string',
        'status' => 'string',
        'payment_id' => 'integer',
        'delivery_address_id' => 'integer',
        'delivery_fee'=>'double',
        'active'=>'boolean',
        'driver_id' => 'integer',
        'is_order_approved' => 'boolean',
        'is_driver_approved' => 'boolean',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
//        'user_id' => 'required|exists:users,id',
        'order_status_id' => 'required|exists:order_statuses,id',
        'payment_id' => 'exists:payments,id',
        'driver_id' => 'nullable|exists:users,id',
    ];

    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',
        'sub_orders'

    ];

    const VENDOR_BASED = 'VENDOR_BASED';
    const PRODUCT_BASED = 'PRODUCT_BASED';

    const PACKAGE_TYPE = 'PACKAGE';
    const PRODUCT_TYPE = 'PRODUCT';
    const ORDER_REQUEST_TYPE = 'ORDER REQUEST';
    const PICKUP_DELIVERY_ORDER_TYPE = 'PICKUP ORDER REQUEST';

    const TEMPORARY_ORDER_REDIRECTION_TYPE = 1;
    const PICKUP_DELIVERY_ORDER_REDIRECTION_TYPE = 2;
    const NEW_ORDER_REDIRECTION_TYPE = 3;
    const MANUAL_ORDER_REDIRECTION_TYPE = 4;
    const ORDER_CANCELLED_REDIRECTION_TYPE = 5;
    const ORDER_APPROVED_REDIRECTION_TYPE = 6;

    const STATUS_RECEIVED = 1;
    const STATUS_PREPARING = 2;
    const STATUS_READY = 3;
    const STATUS_ON_THE_WAY = 4;
    const STATUS_DELIVERED = 5;
    const STATUS_CANCELED = 6;
    const STATUS_DRIVER_ASSIGNED = 7;
    const STATUS_ACCEPTED = 8;

    const PICKED = 'PICKED';
    const DELIVERED = 'DELIVERED';

    public function customFieldsValues()
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

    public function getCustomFieldsAttribute()
    {
        $hasCustomField = in_array(static::class,setting('custom_field_models',[]));
        if (!$hasCustomField){
            return [];
        }
        $array = $this->customFieldsValues()
            ->join('custom_fields','custom_fields.id','=','custom_field_values.custom_field_id')
            ->where('custom_fields.in_table','=',true)
            ->get()->toArray();

        return convertToAssoc($array,'name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function driver()
    {
        return $this->belongsTo(\App\Models\User::class, 'driver_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderDriver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function orderStatus()
    {
        return $this->belongsTo(\App\Models\OrderStatus::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function productOrders()
    {
        return $this->hasMany(\App\Models\ProductOrder::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function products()
    {
        return $this->belongsToMany(\App\Models\Product::class, 'product_orders');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function payment()
    {
        return $this->belongsTo(\App\Models\Payment::class, 'payment_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function deliveryAddress()
    {
        return $this->belongsTo(DeliveryAddress::class, 'delivery_address_id', 'id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function deliveryType()
    {
        return $this->belongsTo(DeliveryType::class, 'delivery_type_id', 'id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/

    public function market()
    {
        return $this->belongsTo(Market::class, 'market_id', 'id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

    public function model()
    {
        return $this->morphMany(MarketTransaction::class, 'model');
    }

    public function packageOrders()
    {
        return $this->hasMany(PackageOrder::class);
    }

    public function order_addons()
    {
        return $this->belongsToMany(\App\Models\ProductAddon::class, 'order_addons', 'order_id', 'product_addon_id');
    }

    public function productOrderRequestOrder()
    {
        return $this->hasOne(ProductOrderRequestOrder::class);
    }

    public function pickUpDeliveryOrder()
    {
        return $this->hasOne(PickUpDeliveryOrder::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Kolkata')->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Kolkata')->format('d-m-Y H:i:s');
    }


    static function orderDeliveredPushNotification($orderId)
    {
        try {

            $userOrder = Order::findOrFail($orderId);
            $userFcmToken = $userOrder->user->device_token;

            $attributes['title'] = 'Owleto Order Delivered';
            $attributes['message'] ='Your order (OrderID : '.$userOrder->id.')  status has been changed to Delivered';
            $attributes['data'] = $userOrder->toArray();

            Notification::route('fcm', $userFcmToken)
                ->notify(new OrderDeliveredPushNotifictaion($attributes));

        }catch (\Exception $e) {

        }
    }

    static function orderDeliveredMail($order)
    {
        try {

            $attributes['email'] = $order->user->email;
            $attributes['order_id'] = $order->id;

            Mail::send( new OrderDeliveredMail($attributes));

        } catch (\Exception $e) {

        }
    }

    static function driverNotification($driver, $id)
    {
        $userOrder = Order::findOrFail($id);

        if($userOrder){

            $correspondingDriver = User::findorFail($driver->user_id);

            if($correspondingDriver){
                $driverFcmToken = $correspondingDriver->device_token;

                $attributes['title'] = 'Owleto Order';
                $attributes['message'] ='Owleto Order with OrderID : '. $userOrder->id .' has been Assigned to you.';
                $attributes['data'] = $userOrder->toArray();

                Notification::route('fcm', $driverFcmToken)
                    ->notify(new DriverAssignedNotification($attributes));
            }

        }

    }

    static function shippedNotification($id)
    {
        $userOrder = Order::findOrFail($id);

        if($userOrder){
            $user = User::findorFail($userOrder->user_id);
            $userFcmToken = $user->device_token;
            // select only order detail  for fcm notification

            $attributes['title'] = 'Owleto Order';
            $attributes['message'] ='Your Order with OrderID ' .$userOrder->id. ' has been Shipped';
            $attributes['data'] = $userOrder->toArray();

            Notification::route('fcm', $userFcmToken)
                ->notify(new DriverAssignedNotificationToUser($attributes));
        }

    }

    public function getSubOrdersAttribute()
    {
        return Order::where('parent_id', $this->id)->get();
    }
}
