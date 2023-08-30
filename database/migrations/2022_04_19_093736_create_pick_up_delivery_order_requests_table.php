<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickUpDeliveryOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pick_up_delivery_order_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->double('delivery_latitude')->nullable();
            $table->double('delivery_longitude')->nullable();
            $table->double('pickup_latitude')->nullable();
            $table->double('pickup_longitude')->nullable();
            $table->longText('address')->nullable();
            $table->double('distance_in_kilometer')->nullable();
            $table->dateTime('pickup_time')->nullable();
            $table->dateTime('expected_delivery_time')->nullable();
            $table->longText('item_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pick_up_delivery_order_requests');
    }
}
