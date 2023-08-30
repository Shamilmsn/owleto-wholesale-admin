<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlotIdToPickUpDeliveryOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pick_up_delivery_order_requests', function (Blueprint $table) {
            $table->integer('slot_id')->after('pick_up_vehicle_id')->nullable();
            $table->string('type')->after('slot_id')->comment('EXPRESS, CUSTOM, SLOT')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pick_up_delivery_order_requests', function (Blueprint $table) {
            $table->dropColumn('slot_id');
            $table->dropColumn('type');
        });
    }
}
