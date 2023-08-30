<?php
/**
 * File name: Product.php
 * Last modified: 2020.05.28 at 19:50:43
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;

/**
 * Class Product
 * @package App\Models
 * @version August 29, 2019, 9:38 pm UTC
 *
 * @property \App\Models\Market market
 * @property \App\Models\Category category
 * @property \Illuminate\Database\Eloquent\Collection[] discountables
 * @property \Illuminate\Database\Eloquent\Collection Option
 * @property \Illuminate\Database\Eloquent\Collection Nutrition
 * @property \Illuminate\Database\Eloquent\Collection ProductsReview
 * @property string id
 * @property string name
 * @property double price
 * @property double discount_price
 * @property string description
 * @property double capacity
 * @property boolean featured
 * @property double package_items_count
 * @property string unit
 * @property integer market_id
 * @property integer category_id
 */
class Product extends Model implements HasMedia
{
    use SoftDeletes;
    use HasMediaTrait {
        getFirstMediaUrl as protected getFirstMediaUrlTrait;
    }

    public $table = 'products';

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'base_name' => 'required',
        'price' => 'required|numeric|min:0',
        'market_id' => 'required|exists:markets,id',
        'category_id' => 'required|exists:categories,id',
        'tax' => 'required',
        'owleto_commission_percentage' => 'required'
    ];

    public $fillable = [
        'base_name',
        'price',
        'discount_price',
        'description',
        'stock',
        'capacity',
        'package_items_count',
        'unit',
        'featured',
        'deliverable',
        'market_id',
        'category_id',
        'sub_category_id',
        'sector_id',
        'is_enabled',
        'scheduled_delivery',
        'order_start_time',
        'order_end_time',
        'delivery_time_id',
        'tax',
        'variant_name',
        'owleto_commission_percentage',
        'food_type',
        'is_approved',
        'minimum_orders',
        'is_flash_sale_approved',
        'flash_sale_end_time',
        'flash_sale_start_time',
        'flash_sale_price',
        'is_flash_sale',
        'is_refund_or_replace',
        'return_days'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'base_name' => 'string',
        'image' => 'string',
        'price' => 'double',
        'discount_price' => 'double',
        'description' => 'string',
        'capacity' => 'double',
        'package_items_count' => 'integer',
        'unit' => 'string',
        'featured' => 'boolean',
        'deliverable' => 'boolean',
        'market_id' => 'integer',
        'category_id' => 'double',
        'tax' => 'double',
        'variant_name' => 'string',
        'owleto_commission_percentage' => 'double',
        'flash_sale_price' => 'double',
        'is_approved' => 'boolean',
        'is_flash_sale_approved' => 'boolean',
        'sector_id' => 'integer',
        'is_flash_sale' => 'boolean',
        'is_refund_or_replace' => 'integer'
    ];
    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',
        'has_media',
        'name',
        'flash_sale_on',
//        'market',
        'is_place_order_available'
    ];

    const BASE_PRODUCT = 1;
    const NOT_BASE_PRODUCT = 0;

    const STANDARD_PRODUCT = 1;
    const VARIANT_PRODUCT = 2;
    const VARIANT_BASE_PRODUCT = 3;

    const VARIANT_PRODUCT_AVAILABLE = 1;

    const TDS_PERCENTAGE = 1;
    const TCS_PERCENTAGE = 1;


    /**
     * @param Media|null $media
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 200, 200)
            ->sharpen(10);

        $this->addMediaConversion('icon')
            ->fit(Manipulations::FIT_CROP, 100, 100)
            ->sharpen(10);
    }

    /**
     * to generate media url in case of fallback will
     * return the file type icon
     * @param string $conversion
     * @return string url
     */
    public function getFirstMediaUrl($collectionName = 'default', $conversion = '')
    {
        $url = $this->getFirstMediaUrlTrait($collectionName);

        $array = explode('.', $url);
        $extension = strtolower(end($array));
        if (in_array($extension, config('medialibrary.extensions_has_thumb'))) {
            return asset($this->getFirstMediaUrlTrait($collectionName, $conversion));
        } else {
            return asset(config('medialibrary.icons_folder') . '/' . $extension . '.png');
        }
    }

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

    public function getNameAttribute()
    {
        $baseName = $this->base_name;

        if(!$this->variant_name) {
            $name = $baseName;
        }else{
             $varaintName = $this->variant_name;
            $name = $baseName .'-'. $varaintName ;
        }

        return $name;
    }
    public function getFlashSaleOnAttribute()
    {
        $now = \Carbon\Carbon::now();
        $date = Carbon::today()->toDateString();
        $is_flash_sale = $this->is_flash_sale;
        $is_flash_sale_approved = $this->is_flash_sale_approved;

        $start_time = Carbon::parse($this->flash_sale_start_time);
        $end_time = Carbon::parse($this->flash_sale_end_time);
        $start_date = $start_time->toDateString();
        $end_date = $end_time->toDateString();

        if( $start_date <= $date && $end_date > $date && $is_flash_sale == true && $is_flash_sale_approved == true) {

            if( $now->between($start_time, $end_time, true )) {
                return true;

            }else {
                return false;
            }
        }else {

            return false;
        }
    }

    public function getisPlaceOrderAvailableAttribute()
    {
       $day = Carbon::today()->format('l');
       $product = Product::findorFail($this->id);
        $product_days = $product->days()->pluck('days.name')->toArray();
        if($product->scheduled_delivery == true ) {
            if(in_array($day,$product_days)) {
                $time = Carbon::now()->toTimeString();

                if ($this->order_start_time <= $time && $time <= $this->order_end_time) {

                    return true;
                }
                return false;
            }else{
                return false;
            }
        }

    }

    public function customFieldsValues()
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

    /**
     * Add Media to api results
     * @return bool
     */
    public function getHasMediaAttribute()
    {
        return $this->hasMedia('image') ? true : false;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class, 'category_id', 'id');
    }

    public function subCategory()
    {
        return $this->belongsTo(\App\Models\Category::class, 'sub_category_id', 'id');
    }

    public function addons()
    {
        return $this->hasMany(ProductAddon::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
//    public function options()
//    {
//        return $this->hasMany(\App\Models\Option::class, 'product_id');
//    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
//    public function optionGroups()
//    {
//        return $this->belongsToMany(\App\Models\OptionGroup::class,'options');
//    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function productReviews()
    {
        return $this->hasMany(\App\Models\ProductReview::class, 'product_id');
    }

    public function productAttributeOptions()
    {
        return $this->hasMany(\App\Models\ProductAttributeOption::class, 'product_id');
    }
//    public function addons()
//    {
//        return $this->hasMany(\App\Models\Addon::class, 'product_id');
//    }

    /**
     * get market attribute
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\BelongsTo|object|null
     */
    public function getMarketAttribute()
    {
        return $this->market()->first(['id', 'name', 'delivery_fee', 'address', 'phone','default_tax','mobile']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function market()
    {
        return $this->belongsTo(\App\Models\Market::class, 'market_id', 'id');
    }

    public function field()
    {
        return $this->belongsTo(\App\Models\Field::class, 'sector_id', 'id');
    }
    public function delivery_time()
    {
        return $this->belongsTo(\App\Models\DeliveryTime::class, 'delivery_time_id', 'id');
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->discount_price > 0 ? $this->discount_price : $this->price;
    }

    /**
     * @return float
     */
    public function applyCoupon($coupon): float
    {
        $price = $this->getPrice();
        if(isset($coupon) && count($this->discountables) + count($this->category->discountables) + count($this->market->discountables) > 0){
            if ($coupon->discount_type == 'fixed') {
                $price -= $coupon->discount;
            } else {
                $price = $price - ($price * $coupon->discount / 100);
            }
            if ($price < 0) $price = 0;
        }
        return $price;
    }

    public function discountables()
    {
        return $this->morphMany('App\Models\Discountable', 'discountable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function days()
    {
        return $this->belongsToMany(\App\Models\Day::class, 'product_scheduled_order_days', 'product_id', 'day_id');
    }

    public function variantProducts()
    {
       return $this->hasMany(Product::class, 'parent_id', 'id')->where('is_variant_display_product', true);
    }


}
