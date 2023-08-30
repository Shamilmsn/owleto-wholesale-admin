<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryTypeIdToPickUpDeliveryOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pick_up_delivery_order_requests', function (Blueprint $table) {
            $table->integer('delivery_type_id')->after('net_amount')->nullable();
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
            $table->dropColumn('delivery_type_id');
        });
    }
}
