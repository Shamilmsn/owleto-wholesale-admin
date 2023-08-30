<?php

use Illuminate\Database\Seeder;

class DaysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        \DB::table('days')->delete();

        \DB::table('days')->insert(array (
            0 =>
                array (
                    'name' => 'Sunday',
                    'day_of_week' => 0,
                    'created_at' => '2022-02-19 16:39:28',
                    'updated_at' => '2022-02-19 18:03:14',
                ),
            1 =>
                array (
                    'name' => 'Monday',
                    'day_of_week' => 1,
                    'created_at' => '2022-02-19 16:39:28',
                    'updated_at' => '2022-02-19 18:03:14',
                ),
            2 =>
                array (
                    'name' => 'Tuesday',
                    'day_of_week' => 2,
                    'created_at' => '2022-02-19 16:39:28',
                    'updated_at' => '2022-02-19 18:03:14',
                ),
            3 =>
                array (
                    'name' => 'Wednesday',
                    'day_of_week' => 3,
                    'created_at' => '2022-02-19 16:39:28',
                    'updated_at' => '2022-02-19 18:03:14',
                ),
            4 =>
                array (
                    'name' => 'Thursday',
                    'day_of_week' => 4,
                    'created_at' => '2022-02-19 16:39:28',
                    'updated_at' => '2022-02-19 18:03:14',
                ),
            5 =>
                array (
                    'name' => 'Friday',
                    'day_of_week' => 5,
                    'created_at' => '2022-02-19 16:39:28',
                    'updated_at' => '2022-02-19 18:03:14',
                ),
            6 =>
                array (
                    'name' => 'Saturday',
                    'day_of_week' => 6,
                    'created_at' => '2022-02-19 16:39:28',
                    'updated_at' => '2022-02-19 18:03:14',
                ),

        ));


    }

}
