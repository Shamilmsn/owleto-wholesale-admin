<?php

use Illuminate\Database\Seeder;
use App\Models\PickUpVehicle;
use Illuminate\Support\Facades\DB;

class PickUpVehileSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        DB::table('pick_up_vehicles')->truncate();

        $pickUpVehicle = new PickUpVehicle();
        $pickUpVehicle->name = 'Bike';
        $pickUpVehicle->amount_per_kilometer = 50;
        $pickUpVehicle->maximum_weight = 10;
        $pickUpVehicle->save();

        $pickUpVehicle = new PickUpVehicle();
        $pickUpVehicle->name = 'Truck';
        $pickUpVehicle->amount_per_kilometer = 100;
        $pickUpVehicle->maximum_weight = 20;
        $pickUpVehicle->save();

    }
}