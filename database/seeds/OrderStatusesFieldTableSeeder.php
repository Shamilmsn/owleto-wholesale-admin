<?php
use App\Models\Field;
use App\Models\OrderStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusesFieldTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('order_status_fields')->delete();

        DB::table('order_status_fields')->insert(array (
            0 =>
                array (
                    'order_status_id' => OrderStatus::STATUS_RECEIVED,
                    'field_id' => Field::PICKUP_DELIVERY
                ),
            1 =>
                array (
                    'order_status_id' => OrderStatus::STATUS_ON_THE_WAY,
                    'field_id' => Field::PICKUP_DELIVERY
                ),
            2 =>
                array (
                    'order_status_id' => OrderStatus::STATUS_DRIVER_ASSIGNED,
                    'field_id' => Field::PICKUP_DELIVERY
                ),
            3 =>
                array (
                    'order_status_id' => OrderStatus::STATUS_DELIVERED,
                    'field_id' => Field::PICKUP_DELIVERY
                ),
            4 =>
                array (
                    'order_status_id' => OrderStatus::STATUS_CANCELED,
                    'field_id' => Field::PICKUP_DELIVERY
                ),
            5 =>
                array (
                    'order_status_id' => OrderStatus::STATUS_RECEIVED,
                    'field_id' => Field::TAKEAWAY
                ),
            6 =>
                array (
                    'order_status_id' => OrderStatus::STATUS_DELIVERED,
                    'field_id' => Field::TAKEAWAY
                ),
            7 =>
                array (
                    'order_status_id' => OrderStatus::STATUS_CANCELED,
                    'field_id' => Field::TAKEAWAY
                ),
        ));
    }
}
