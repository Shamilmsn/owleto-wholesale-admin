<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageDeliveryTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_delivery_times', function (Blueprint $table) {
            $table->id();
            $table->integer('package_id')->unsigned();
            $table->integer('delivery_time_id')->unsigned();
            $table->timestamps();
            $table->foreign('package_id')->references('id')->on('subscription_packages');
            $table->foreign('delivery_time_id')->references('id')->on('delivery_times');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_delivery_times');
    }
}
