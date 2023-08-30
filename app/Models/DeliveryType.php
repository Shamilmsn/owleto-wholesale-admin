<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;

class DeliveryType extends Model
{
    public $table = 'delivery_types';

    public $fillable = [
        'name',
        'charge',
        'base_distance',
        'additional_amount',
        'start_time',
        'end_time',
        'isTimeType',
        'display_time_start_at',
        'display_time_end_at'
    ];

    const EXPRESS = 'EXPRESS';
    const CUSTOM = 'CUSTOM';
    const SLOT = 'SLOT';

    const MNG_SLOT = 1;
    const TYPE_EXPRESS = 2;
    const TAKE_AWAY = 3;
    const EVNG_SLOT = 3;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'charge' => 'double',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required',
        'charge' => 'required',
        'base_distance' => 'required',
        'additional_amount' => 'required'
    ];

    public function customFieldsValues()
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

    public function sectors()
    {
        return $this->belongsToMany(Field::class, 'sector_delivery_types', 'field_id', 'delivery_type_id');
    }

    public function sectorDeliveryTypes()
    {
        return $this->belongsToMany(Field::class, 'sector_delivery_types', 'delivery_type_id','field_id');
    }
}
