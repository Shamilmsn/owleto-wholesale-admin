<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent as Model;

/**
 * Class Driver
 * @package App\Models
 * @version March 25, 2020, 9:47 am UTC
 *
 * @property \App\Models\User user
 * @property integer user_id
 * @property double delivery_fee
 * @property integer total_orders
 * @property double earning
 * @property boolean available
 */
class Driver extends Model
{

    public $table = 'drivers';
    public $primaryKey = 'id';

    public $fillable = [
        'user_id',
        'delivery_fee',
        'additional_amount',
        'base_distance',
        'total_orders',
        'earning',
        'balance',
        'available',
        'city_id',
        'vehicle_id',
        'circle_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'delivery_fee' => 'double',
        'total_orders' => 'integer',
        'earning' => 'double',
        'available' => 'boolean'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
      //  'delivery_fee' => 'required'
        //'user_id' => 'required|exists:users,id'
    ];

    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',
        'pending_delivery',
        'completed_delivery',
        'driver_signup_status'
    ];

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

    public function getpendingDeliveryAttribute()
    {
        $userId = $this->user_id;

        $orders = Order::where('driver_id', $userId)
            ->whereDate('driver_assigned_at', Carbon::today())
            ->where('type', '<>', Order::PACKAGE_TYPE)
            ->whereIn('order_status_id', [Order::STATUS_DRIVER_ASSIGNED,Order::STATUS_ON_THE_WAY])
            ->get();

        $packageOrders = PackageOrder::where('driver_id', $userId)
            ->whereDate('driver_assigned_at', Carbon::today())
            ->whereIn('order_status_id', [Order::STATUS_DRIVER_ASSIGNED,Order::STATUS_ON_THE_WAY])
            ->get();

        $totalOrder = count($orders) + count($packageOrders);

        return $totalOrder;
    }

    public function getcompletedDeliveryAttribute()
    {
        $userId = $this->user_id;

        $orders = Order::where('driver_id', $userId)
            ->where('type','<>', Order::PACKAGE_TYPE)
            ->whereDate('driver_assigned_at', Carbon::today())
            ->where('order_status_id', Order::STATUS_DELIVERED)
            ->get();

        $packageOrders = PackageOrder::where('driver_id', $userId)
            ->whereDate('driver_assigned_at', Carbon::today())
            ->where('order_status_id', Order::STATUS_DELIVERED)
            ->get();

        $totalOrder = count($orders) + count($packageOrders);

        return $totalOrder;

    }

    public function getdriverSignupStatusAttribute()
    {
        $user = User::find($this->user_id);

        return $user->driver_signup_status;
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'circle_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(PickUpVehicle::class, 'vehicle_id');
    }

    static function updateDriver($orderDriverId, $driverCommissionAmount)
    {
        $driverTotalOrders = Order::where('driver_id', $orderDriverId)
            ->where('order_status_id', Order::STATUS_DELIVERED)
            ->where('type', '<>', Order::PACKAGE_TYPE)
            ->count();

        $driverTotalPackageOrders = PackageOrder::where('driver_id', $orderDriverId)->count();

        $totalOrdersCount = $driverTotalOrders + $driverTotalPackageOrders;
        $driver = Driver::where('user_id', $orderDriverId)->first();

        if($driver){
            $driver->earning = $driver->earning + $driverCommissionAmount;
            $driver->balance = $driver->balance + $driverCommissionAmount;
            $driver->total_orders = $totalOrdersCount;
            $driver->available = 1;
            $driver->save();
        }
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'driver_id', 'user_id');
    }
    
}
