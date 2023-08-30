<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Earning
 * @package App\Models
 * @version March 25, 2020, 9:48 am UTC
 *
 * @property \App\Models\Market market
 * @property integer market_id
 * @property integer total_orders
 * @property double total_earning
 * @property double admin_earning
 * @property double market_earning
 * @property double delivery_fee
 * @property double tax
 */
class Earning extends Model
{

    public $table = 'earnings';
    


    public $fillable = [
        'market_id',
        'total_orders',
        'total_earning',
        'admin_earning',
        'market_earning',
        'delivery_fee',
        'tax'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'market_id' => 'integer',
        'total_orders' => 'integer',
        'total_earning' => 'double',
        'admin_earning' => 'double',
        'market_earning' => 'double',
        'delivery_fee' => 'double',
        'tax' => 'double'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'market_id' => 'required|exists:markets,id'
    ];

    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',
        
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
    public function market()
    {
        return $this->belongsTo(\App\Models\Market::class, 'market_id', 'id');
    }

    static function createOrUpdate($marketId)
    {
        $earning = Earning::where('market_id', $marketId)->first();

//        $OrderCount = Order::where('market_id', $marketId)
//            ->where('type', '<>', Order::PACKAGE_TYPE)
//            ->where('order_status_id', Order::STATUS_DELIVERED)
//            ->count();
//
//        $packageOrderCount = PackageOrder::where('market_id', $marketId)
//            ->where('order_status_id', Order::STATUS_DELIVERED)
//            ->count();

        $totalOrdersCount = $earning->total_orders + 1;

//        $marketPackageEarning = PackageOrder::where('market_id', $marketId)
//            ->where('order_status_id', Order::STATUS_DELIVERED)
//            ->sum('market_balance');
//
//        $marketOrderEarning = Order::where('market_id', $marketId)
//            ->where('type', '<>', Order::PACKAGE_TYPE)
//            ->where('order_status_id', Order::STATUS_DELIVERED)
//            ->sum('market_balance');

        $marketTotalEarnings = $earning->market_earning + $marketPackageEarning;

        $adminOrderEarning = Order::where('market_id', $marketId)
            ->where('order_status_id', Order::STATUS_DELIVERED)
            ->where('type', '<>', Order::PACKAGE_TYPE)
            ->sum('owleto_commission_amount');

        $adminPackageOrderEarning = PackageOrder::where('market_id', $marketId)
            ->where('order_status_id', Order::STATUS_DELIVERED)
            ->sum('commission_amount');

        $totalAdminEarnings = $adminOrderEarning + $adminPackageOrderEarning;

        $totalAmountFromOrders = Order::where('market_id', $marketId)
            ->where('order_status_id', Order::STATUS_DELIVERED)
            ->where('type', '<>', Order::PACKAGE_TYPE)
            ->sum('total_amount');

        $totalAmountFromPackageOrder = PackageOrder::where('market_id', $marketId)
            ->where('order_status_id', Order::STATUS_DELIVERED)
            ->sum('price_per_delivery');

        $totalEarning = $totalAmountFromOrders + $totalAmountFromPackageOrder;

        $earning = Earning::where('market_id', $marketId)->first();

        if(!$earning){
            $earning = new Earning();
        }

        $earning->market_id = $marketId;
        $earning->total_orders = $totalOrdersCount;
        $earning->total_earning = $totalEarning;
        $earning->admin_earning = $totalAdminEarnings;
        $earning->market_earning = $marketTotalEarnings;
        $earning->save();
    }
    
}
