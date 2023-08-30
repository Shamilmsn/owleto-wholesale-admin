<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;

class TemporaryOrderRequest extends Model implements HasMedia
{
    use HasFactory;

    use HasMediaTrait {
        getFirstMediaUrl as protected getFirstMediaUrlTrait;
    }

    protected $table = 'temporary_order_requests';

    public $fillable = [
        'user_id',
        'order_request_id',
        'net_amount',
        'status',
        'distance',
        'bill_image'
    ];

    public static $rules = [
        'order_request_id' => 'required',
        'user_id' => 'required',
        'net_amount'  => 'required',

    ];
    protected $appends = [
        'has_media',
        'bill_image_url'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'order_status_id' => 'integer',
        'net_amount' => 'double',
        'status' => 'string',
    ];

    const NOTIFICATION_SEND = 1;
    const PAYMENT_INITIATED = 2;
    const ORDER_CREATED = 3;
    const PAYMENT_SUCCESS = 4;

    static $statuses = [
        1 => 'Notification Send',
        2 => 'payment initiated',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 200, 200)
            ->sharpen(10);

        $this->addMediaConversion('icon')
            ->fit(Manipulations::FIT_CROP, 100, 100)
            ->sharpen(10);
    }
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
    public function getHasMediaAttribute()
    {
        return $this->hasMedia('image') ? true : false;
    }

    public function orderRequest()
    {
        return $this->belongsTo(OrderRequest::class);
    }

    public function productOrderRequest()
    {
        return $this->hasOne(ProductOrderRequestOrder::class);
    }

    public function getBillImageUrlAttribute()
    {
        if(!$this->bill_image){
            return;
        }
        return Storage::disk('public')->url('order-requests/bill-images/'.$this->bill_image);
    }


}
