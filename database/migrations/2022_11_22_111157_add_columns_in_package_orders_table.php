<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInPackageOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_orders', function (Blueprint $table) {
            $table->double('driver_base_km')->after('distance');
            $table->double('driver_additional_km_price')->after('driver_base_km');
            $table->double('driver_total_distance')->after('driver_additional_km_price');
            $table->boolean('is_driver_approved')->after('driver_additional_km_price')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_orders', function (Blueprint $table) {
            $table->double('driver_base_km');
            $table->double('driver_additional_km_price');
            $table->double('driver_total_distance');
            $table->boolean('is_driver_approved');
        });
    }
}
