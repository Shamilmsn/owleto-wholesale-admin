<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickUpDeliveryOrderRequest extends Model
{
    use HasFactory;

    public $table = 'pick_up_delivery_order_requests';

    const STATUS_PENDING = 'PENDING';
    const STATUS_ACCEPTED = 'ACCEPTED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_ORDER_CREATED = 'ORDER CREATED';
    const STATUS_ORDER_PAID = 'PAID';

    public $fillable = [
        'user_id',
        'name',
        'phone',
        'delivery_latitude',
        'delivery_longitude',
        'pickup_latitude',
        'pickup_longitude',
        'pickup_address',
        'delivery_address',
        'distance_in_kilometer',
        'pickup_time',
        'item_description',
        'pick_up_vehicle_id',
        'status',
        'audio_file',
        'type',
        'slot_id',
        'net_amount',
        'delivery_type_id'
    ];

    protected $appends = [
        'audio_file_url'
    ];

    protected $casts = [
        'delivery_latitude' => 'double',
        'delivery_longitude' => 'double',
        'pickup_latitude' => 'double',
        'pickup_longitude' => 'double',
        'distance_in_kilometer' => 'double',
        'pick_up_vehicle_id' => 'integer',
        'slot_id' => 'integer',
    ];

    public function pickUpVehicle()
    {
        return $this->belongsTo(PickUpVehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function slot()
    {
        return $this->belongsTo(Slot::class);
    }
    public function pickupDeliveryOrder()
    {
        return $this->hasOne(PickUpDeliveryOrder::class);
    }

    public function getaudioFileUrlAttribute()
    {
        if(!$this->audio_file){
            return null;
        }
        return url('storage/pickup-requests/audios') . '/' . $this->audio_file;
    }
}
