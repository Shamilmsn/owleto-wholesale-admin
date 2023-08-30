<?php

use Illuminate\Database\Seeder;

class DeliveryTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('delivery_times')->delete();

        DB::table('delivery_times')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Morning',
                'created_at' => '2022-02-06 15:30:23',
                'updated_at' => '2022-02-06 16:23:20',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'Evening',
                'created_at' => '2022-02-06 15:30:23',
                'updated_at' => '2022-02-06 16:23:20',
            ),
        ));
    }
}
