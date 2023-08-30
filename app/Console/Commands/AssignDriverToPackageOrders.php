<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\DriversCurrentLocation;
use App\Models\Market;
use App\Models\Order;
use App\Models\PackageOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Contract\Database;

class AssignDriverToPackageOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'driver:assign-to-package-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign drivers to orders';

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
        $currentDateTime = Carbon::now();
        $currentHour = $currentDateTime->hour;

        $mngStartTime = config('services.driver-assign-time.driver_assign_morning_start');
        $mngEndTime = config('services.driver-assign-time.driver_assign_morning_end');
        $evngStartTime = config('services.driver-assign-time.driver_assign_evening_start');
        $evngEndTime = config('services.driver-assign-time.driver_assign_evening_end');

        $orders = [];

        if($currentHour > $mngStartTime && $currentHour < $mngEndTime) {

            $orders = PackageOrder::whereIn('order_status_id',[Order::STATUS_RECEIVED, Order::STATUS_PREPARING, Order::STATUS_READY])
                ->whereDate('date', Carbon::today())
                ->where('delivery_time_id', 1)
                ->whereNull('driver_id')
                ->get();
        }

        if($currentHour > $evngStartTime && $currentHour < $evngEndTime) {

            $orders = PackageOrder::whereIn('order_status_id',[Order::STATUS_RECEIVED, Order::STATUS_PREPARING, Order::STATUS_READY])
                ->whereDate('date', Carbon::today())
                ->where('delivery_time_id', 2)
                ->whereNull('driver_id')
                ->get();
        }

        if(count($orders)>0){

            foreach ($orders as $order){

                $market = Market::where('id', $order->market_id)->first();

                $latMarket = $market->latitude;
                $longMarket = $market->longitude;

                $references = $this->database->getReference($this->table)->getValue();

                foreach ($references as $reference){
                    if (array_key_exists("user_id", $reference)) {

                        $currentDriverLatitude = $reference['latitude'];
                        $currentDriverLongitude = $reference['longitude'];

                        if (DriversCurrentLocation::getDriverCurrentLocations($latMarket, $longMarket, $currentDriverLatitude,
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

                $driversCurrentLocations = DriversCurrentLocation::getAvailableDriver($latMarket, $longMarket, $market);

                if($driversCurrentLocations){

                    $this->updateDriverToOrder($order, $driversCurrentLocations);

                }
                else{

                    if($order->created_at < Carbon::now()->subMinutes(3)){
                        $driversCurrentLocations = DriversCurrentLocation::getAvailableDriver($latMarket, $longMarket, null);

                        if($driversCurrentLocations){

                            $this->updateDriverToOrder($order, $driversCurrentLocations);
                        }
                    }
                }
            }
        }
    }

    public function updateDriverToOrder($order, $driversCurrentLocations)
    {
        $driver = Driver::where('id', $driversCurrentLocations->driver_id)->first();
//        $distance = $order->distance;

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

        if($driver){
            $driver->available = 0;
            $driver->save();

            Order::driverNotification($driver, $order->id);
            Order::shippedNotification($order->id);
        }
    }
}
