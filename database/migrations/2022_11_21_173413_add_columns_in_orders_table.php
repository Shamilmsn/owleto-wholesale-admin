<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->double('driver_base_km')->after('distance');
            $table->double('driver_additional_km_price')->after('driver_base_km');
            $table->double('driver_total_distance')->after('driver_additional_km_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->double('driver_base_km');
            $table->double('driver_additional_km_price');
            $table->double('driver_total_distance');
        });
    }
}
