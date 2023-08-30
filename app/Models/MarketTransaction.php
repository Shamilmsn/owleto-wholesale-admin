<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketTransaction extends Model
{
    use HasFactory;

    public function model()
    {
        return $this->morphTo();
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Kolkata')->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Kolkata')->format('d-m-Y H:i:s');
    }
    
    static function store($marketId, $order)
    {
        $totalCredit = MarketTransaction::where('market_id', $marketId)->sum('credit');
        $totalDebit = MarketTransaction::where('market_id', $marketId)->sum('debit');
        $totalCredit = $totalCredit + $order->market_balance;

        $transaction = new MarketTransaction();
        $transaction->market_id = $marketId;
        $transaction->credit = $order->market_balance;
        $transaction->balance = $totalCredit - $totalDebit;
        $transaction->model()->associate($order);
        $transaction->save();
    }
}
