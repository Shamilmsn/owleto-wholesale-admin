<?php

namespace App\Http\Controllers\API\Driver;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PackageOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DriverApproveOrderController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'order_id' => 'required',
            'distance' => 'required',
            'type' => 'required'
        ]);

        if ($request->type == Order::PACKAGE_TYPE) {
            $order = PackageOrder::findOrFail($request->order_id);
        } else {
            $order = Order::findOrFail($request->order_id);
        }

        $driver = $order->orderDriver;
        $distanceToVendor = $request->distance; // distance from driver to vendor or pickup location

        $distance = $order->distance + $distanceToVendor;  //  14.734365489879

        if ($distance <= $driver->base_distance) {
            $driverCommissionAmount = $driver->delivery_fee; //20
        } else {
            $additionalDistance = $distance - $driver->base_distance; // 14.734365489879 - 2 = 12.734365489879
            $driverCommissionAmount = $driver->delivery_fee + ($additionalDistance * $driver->additional_amount); // 20 + (12.734365489879 * 5) = 83.671827449
        }

        $order->driver_base_km = $driver->base_distance;
        $order->driver_additional_km_price = $driver->additional_amount;
        $order->driver_commission_amount = round($driverCommissionAmount,2);
        $order->driver_total_distance = $distance;
        $order->is_driver_approved = true;
        $order->update();

        return $this->sendResponse($order, 'driver order accepted');
    }

}