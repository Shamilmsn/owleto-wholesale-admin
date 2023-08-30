<?php

namespace App\Console\Commands;

use App\Models\DeliveryType;
use App\Models\Driver;
use App\Models\DriversCurrentLocation;
use App\Models\Order;
use App\Models\PickUpDeliveryOrder;
use App\Models\PickUpDeliveryOrderRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Kreait\Firebase\Contract\Database;

class AssignDriverToPickUpOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'driver:assign-to-pick-up-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign drivers to pickup orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Database $database)
    {
        parent::__construct();

        $this->database = $database;
        $this->table = 'user_locations';
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $orders = Order::where('delivery_type_id', '=', DeliveryType::TYPE_EXPRESS)
            ->whereIn('order_status_id', [Order::STATUS_RECEIVED, Order::STATUS_PREPARING, Order::STATUS_READY])
            ->where('type', Order::PICKUP_DELIVERY_ORDER_TYPE)
            ->whereNull('driver_id')
            ->get();

        foreach ($orders as $order) {

            $pickUpOrder = PickUpDeliveryOrder::where('order_id', $order->id)->first();
            $pickUpOrderRequest = PickUpDeliveryOrderRequest::where('id', $pickUpOrder->pick_up_delivery_order_request_id)->first();

            $pickupLat = $pickUpOrderRequest->pickup_latitude;
            $pickupLong = $pickUpOrderRequest->pickup_longitude;

            $references = $this->database->getReference($this->table)->getValue();

            foreach ($references as $reference) {

                if (array_key_exists("user_id", $reference)) {

                    $currentDriverLatitude = $reference['latitude'];
                    $currentDriverLongitude = $reference['longitude'];

                    if (DriversCurrentLocation::getDriverCurrentLocations($pickupLat, $pickupLong, $currentDriverLatitude,
                            $currentDriverLongitude, "K") < 10) {

                        $driver = Driver::where('user_id', $reference['user_id'])->first();

                        if ($driver) {
                            $driverId = $driver->id;
                            DriversCurrentLocation::updateCurrentLocation($driverId,
                                $currentDriverLatitude, $currentDriverLongitude);
                        }
                    }
                }

            }

            $driversCurrentLocations = DriversCurrentLocation::getAvailableDriver($pickupLat, $pickupLong, null);

            if ($driversCurrentLocations) {

                $this->updateDriverToOrder($order, $driversCurrentLocations);
            } else {

                if ($order->created_at < Carbon::now()->subMinutes(3)) {
                    $driversCurrentLocations = DriversCurrentLocation::getAvailableDriver($pickupLat, $pickupLong, null);

                    if ($driversCurrentLocations) {

                        $this->updateDriverToOrder($order, $driversCurrentLocations);
                    }
                }
            }
        }
    }

    public function updateDriverToOrder($order, $driversCurrentLocations)
    {
        $driver = Driver::where('id', $driversCurrentLocations->driver_id)->first();
        $distance = $order->distance;

//        if ($distance <= $driver->base_distance) {
//            $driverCommissionAmount = $driver->delivery_fee;
//        }
//        else {
//            $additionalDistance = $order->distance - $driver->base_distance;
//            $driverCommissionAmount = $driver->delivery_fee + $additionalDistance * $driver->additional_amount;
//        }

        $order->order_status_id = Order::STATUS_DRIVER_ASSIGNED;
        $order->driver_id = $driversCurrentLocations->driver->user_id;
        $order->driver_assigned_at = Carbon::now();
//        $order->driver_commission_amount = $driverCommissionAmount;
        $order->save();

        if ($driver) {
            $driver->available = 0;
            $driver->save();

            Order::driverNotification($driver, $order->id);
            Order::shippedNotification($order->id);
        }
    }
}
