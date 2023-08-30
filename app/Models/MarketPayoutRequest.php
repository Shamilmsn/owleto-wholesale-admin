<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketPayoutRequest extends Model
{
    use HasFactory;

    const PENDING = 'PENDING';
    const PAID = 'PAID';
    const REJECTED = 'REJECTED';
    const PARTIAL = 'PARTIALLY PAID';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }
}
