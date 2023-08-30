<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryTypeIdDeliveryAddressIdAndDeliveryFeeToOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_requests', function (Blueprint $table) {

            $table->integer('delivery_type_id')->nullable()->after('status');
            $table->integer('delivery_address_id')->nullable()->after('status');
            $table->integer('delivery_fee')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_requests', function (Blueprint $table) {
            $table->dropColumn('delivery_address_id');
            $table->dropColumn('delivery_fee');
            $table->dropColumn('delivery_type_id');
        });
    }
}
