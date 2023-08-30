<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class OrderStatus
 * @package App\Models
 * @version August 29, 2019, 9:38 pm UTC
 *
 * @property string status
 */
class OrderStatus extends Model
{

    public $table = 'order_statuses';

    const STATUS_RECEIVED = 1;
    const STATUS_PREPARING = 2;
    const STATUS_READY = 3;
    const STATUS_ON_THE_WAY = 4;
    const STATUS_DELIVERED = 5;
    const STATUS_CANCELED = 6;
    const STATUS_DRIVER_ASSIGNED = 7;
    const STATUS_ACCEPTED = 8;

    public $fillable = [
        'status'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'status' => 'required'
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function orderStatusFields()
    {
        return $this->belongsToMany(\App\Models\Field::class, 'order_status_fields', 'order_status_id', 'field_id');
    }

    
    
}
