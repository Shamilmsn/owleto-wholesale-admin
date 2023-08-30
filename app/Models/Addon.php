<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    public function product()
    {
        return $this->BelongsTo(\App\Models\Product::class, 'product_id');
    }
}
