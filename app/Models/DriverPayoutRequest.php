<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverPayoutRequest extends Model
{
    use HasFactory;

    protected $table = 'driver_payout_requests';
    public $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'amount',
    ];

    const PAID = 'PAID';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
