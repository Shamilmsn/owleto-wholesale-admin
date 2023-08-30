<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWalletTransaction extends Model
{
    use HasFactory;

    const TYPE_CREDIT = 'CREDIT';
    const TYPE_DEBIT = 'DEBIT';

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Kolkata')->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Kolkata')->format('d-m-Y H:i:s');
    }
}
