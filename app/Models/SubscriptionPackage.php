<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPackage extends Model
{
    use SoftDeletes;
    public $table = 'subscription_packages';

    public static $rules = [
        'name' => 'required',
        'product_id' => 'required|exists:products,id',
        'market_id' => 'required|exists:markets,id',
        'price' => 'required',
        'package_days' => 'required'
    ];

    public $fillable = [
        'name',
        'product_id',
        'quantity',
        'days',
        'market_id',
        'price',
        'description',
        'actual_price',
    ];

    protected $casts = [
        'name' => 'string',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'days' => 'double',
        'market_id' => 'integer',
        'price' => 'double',
        'description' => 'string'
    ];

    public function getCustomFieldsAttribute()
    {
        $hasCustomField = in_array(static::class, setting('custom_field_models', []));
        if (!$hasCustomField) {
            return [];
        }
        $array = $this->customFieldsValues()
            ->join('custom_fields', 'custom_fields.id', '=', 'custom_field_values.custom_field_id')
            ->where('custom_fields.in_table', '=', true)
            ->get()->toArray();

        return convertToAssoc($array, 'name');
    }

    public function customFieldsValues()
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }
    public function market()
    {
        return $this->belongsTo(\App\Models\Market::class, 'market_id', 'id');
    }
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    public function package_days()
    {
        return $this->belongsToMany(\App\Models\Day::class, 'package_days','package_id','day_id');
    }
    public function package_delivery_times()
    {
        return $this->belongsToMany(\App\Models\DeliveryTime::class, 'package_delivery_times','package_id','delivery_time_id');
    }

}
