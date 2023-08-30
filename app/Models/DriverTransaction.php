<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverTransaction extends Model
{
    use HasFactory;

    protected $table = 'driver_transactions';

    const TYPE_CREDIT = 'CREDIT';
    const TYPE_DEBIT = 'DEBIT';

    public function model()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Kolkata')->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Kolkata')->format('d-m-Y H:i:s');
    }

    static function store($orderDriverId, $order, $driverCommissionAmount)
    {
        $driver = Driver::where('user_id', $orderDriverId)->first();

        $balance = $driver->balance + $driverCommissionAmount;

        $driverTransaction = new DriverTransaction();
        $driverTransaction->user_id = $orderDriverId;
        $driverTransaction->type = DriverTransaction::TYPE_CREDIT;
        $driverTransaction->credit = $driverCommissionAmount;
        $driverTransaction->description = 'Amount Credited';
        $driverTransaction->balance = $balance;
        $driverTransaction->model()->associate($order);
        $driverTransaction->save();
    }
}
