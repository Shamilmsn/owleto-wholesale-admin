<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwletoEarning extends Model
{
    use HasFactory;

    static function create($order, $orderType)
    {
        $owletoEarning = new OwletoEarning();
        $owletoEarning->order_id = $order->id;
        $owletoEarning->order_type = $orderType;
        $owletoEarning->earning = round($order->owleto_commission_amount,2);
        $owletoEarning->save();
    }
}
