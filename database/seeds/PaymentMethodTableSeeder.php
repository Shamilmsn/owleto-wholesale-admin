<?php

use Illuminate\Database\Seeder;

class PaymentMethodTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('payment_methods')->delete();

        \DB::table('payment_methods')->insert(array (
            0 =>
                array (
                    'name' => 'Cash on delivery',
                    'is_active' => true,
                    'created_at' => '2022-02-19 16:39:28',
                    'updated_at' => '2022-02-19 18:03:14',
                ),
            1 =>
                array (
                    'name' => 'Razorpay',
                    'is_active' => true,
                    'created_at' => '2022-02-19 16:39:28',
                    'updated_at' => '2022-02-19 18:03:14',
                )
        ));
    }
}