<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class CashController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $orderId = $request->orderId;
        $order = Order::find($orderId);
        if($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_COD) {
            $order->is_collected_from_driver = true;
            $order->save();

            $driver = Driver::where('user_id', $order->driver_id)->first();
            $driver->balance_cod_amount = $driver->balance_cod_amount - $order->total_amount;
            $driver->save();
        }

        return response()->json(true, 200);
    }

}
