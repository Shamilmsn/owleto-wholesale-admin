<?php

use Illuminate\Database\Seeder;

class WidgetTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('widgets')->delete();

        \Illuminate\Support\Facades\DB::table('widgets')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Size',
                'created_at' => '2022-02-24 22:54:23',
                'updated_at' =>  '2022-02-24 22:54:23',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'Color',
                'created_at' =>  '2022-02-24 22:54:23',
                'updated_at' =>  '2022-02-24 22:54:23',
            ),

        ));


    }
}
