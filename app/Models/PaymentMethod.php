<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    public $table = 'payment_methods';

    const PAYMENT_METHOD_COD = 1;
    const PAYMENT_METHOD_RAZORPAY = 2;
    const PAYMENT_METHOD_WALLET = 3;

    const RAZORPAY = 'RAZORPAY';
    const CASH_ON_DELIVERY = 'CASH ON DELIVERY';
    const WALLET = 'WALLET';

    public $fillable = [
        'name',
        'is_active',
    ];

    public function market_payment_methods()
    {
        return $this->belongsToMany(\App\Models\Market::class, 'market_payment_methods');
    }

}
