<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlotedDeliveryDriverHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sloted_delivery_driver_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->nullable();
            $table->integer('picked_up_driver_id')->nullable();
            $table->dateTime('picked_up_driver_assigned_at')->nullable();
            $table->integer('delivered_driver_id')->nullable();
            $table->dateTime('delivered_driver_assigned_at')->nullable();
            $table->string('status')
                ->comment('PICKUP_ASSIGNED, 
                PICKED, DROPPED, DELIVER_ASSIGNED, DELIVERED, CANCELED')
                ->nullable();
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
        Schema::dropIfExists('sloted_delivery_driver_histories');
    }
}
