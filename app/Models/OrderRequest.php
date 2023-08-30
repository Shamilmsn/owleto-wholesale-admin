<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\HasMedia\HasMedia;


class OrderRequest extends Model implements HasMedia
{
    use HasFactory;
    use HasMediaTrait {
        getFirstMediaUrl as protected getFirstMediaUrlTrait;
    }

    public $table = 'order_requests';

    public $fillable = [
        'user_id',
        'market_id',
        'sector_id',
        'order_id',
        'type',
        'order_text',
        'file',
        'status',
        'reviewed_by',
        'image',
        'delivery_type_id',
        'delivery_address_id',
        'delivery_fee',
        'distance'
    ];

    public static $rules = [

        'user_id' => 'required',
        'market_id' => 'required',
        'sector_id' => 'required',
        'type'  => 'required',

    ];

    protected $appends = [
//        'custom_fields',
//        'has_media',
        'image_url',
        'payment_method_id'
    ];

    const STATUS_NEW = 'NEW';
    const STATUS_CONTACTED = 'CONTACTED';
    const STATUS_ADD_TO_CART = 'ADDED TO CART';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_ORDER_CREATED = 'ORDER CREATED';
    const STATUS_ORDER_PAID = 'PAID';
    const STATUS_NOTIFICATION_SEND = 'ORDER APPROVED';
    const STATUS_PAYMENT_INITIATED = 'PAYMENT INITIATED';

    static $statuses = [
        1 => 'NOTIFICATION SEND',
        2 => 'PAYMENT INITIATED',

    ];

    const TEXT = 1;
    const IMAGE = 2;

    static $types = [
        1 => 'Text Description',
        2 => 'Image',
        3 => 'Image & Text'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'market_id' => 'integer',
        'sector_id' => 'integer',
        'type' => 'integer',
        'order_id' => 'integer'
    ];

    public function getImageUrlAttribute()
    {
        if(!$this->image){
            return;
        }
        return url('storage/order-requests/images/'.$this->image);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function review()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function sector()
    {
        return $this->belongsTo(Field::class);
    }

    public function deliveryType()
    {
        return $this->belongsTo(DeliveryType::class);
    }

    public function temporaryOrderRequest()
    {
        return $this->hasOne(TemporaryOrderRequest::class);
    }

    public function getPaymentMethodIdAttribute()
    {
        if($this->temporaryOrderRequest){
            if ($this->temporaryOrderRequest->productOrderRequest){
                return $this->temporaryOrderRequest->productOrderRequest->order->payment_method_id;
            }
        }

        return null;
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Kolkata')->format('d-m-Y H:i:s');
    }



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

    public function customFieldsValues()
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

    /**
     * to generate media url in case of fallback will
     * return the file type icon
     * @param string $conversion
     * @return string url
     */
    public function getFirstMediaUrl($collectionName = 'default',$conversion = '')
    {
        $url = $this->getFirstMediaUrlTrait($collectionName);
        $array = explode('.', $url);
        $extension = strtolower(end($array));
        if (in_array($extension,config('medialibrary.extensions_has_thumb'))) {
            return asset($this->getFirstMediaUrlTrait($collectionName,$conversion));
        }else{
            return asset(config('medialibrary.icons_folder').'/'.$extension.'.png');
        }
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
     * Add Media to api results
     * @return bool
     */
    public function getHasMediaAttribute()
    {
        return $this->hasMedia('image') ? true : false;
    }


}
