<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttributeOption extends Model
{
    use SoftDeletes;
    protected $table = 'product_attribute_options';

    public $fillable = [
        'product_id',
        'attribute_id',
        'attribute_option_id',
        'base_product_id',

    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id', 'id');
    }
    public function attribute()
    {
        return $this->belongsTo(\App\Models\Attribute::class, 'attribute_id', 'id');
    }
    public function attributeOption()
    {
        return $this->belongsTo(\App\Models\AttributeOption::class, 'attribute_option_id', 'id');
    }
}
