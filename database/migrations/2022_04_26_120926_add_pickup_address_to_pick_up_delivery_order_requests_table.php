<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPickupAddressToPickUpDeliveryOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pick_up_delivery_order_requests', function (Blueprint $table) {
            $table->longText('pickup_address')->after('pickup_longitude')->nullable();
            $table->renameColumn('address', 'delivery_address');
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
            $table->dropColumn('pickup_address');
            $table->renameColumn('delivery_address', 'address');
        });
    }
}
