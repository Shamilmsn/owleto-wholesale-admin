<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraFieldsToPackageOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_orders', function (Blueprint $table) {
            $table->integer('user_id')->after('order_id')->nullable();
            $table->integer('order_status_id')->after('user_id')->nullable();
            $table->integer('market_id')->after('order_status_id')->nullable();
            $table->integer('delivery_address_id')->after('market_id')->nullable();
            $table->integer('payment_method_id')->after('delivery_address_id')->nullable();
            $table->double('distance')->after('payment_method_id')->nullable();
            $table->integer('driver_id')->after('distance')->nullable();
            $table->double('driver_commission_amount')->after('driver_id')->nullable();
            $table->double('tax')->after('driver_commission_amount')->nullable();
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
            $table->dropColumn('user_id');
            $table->dropColumn('order_status_id');
            $table->dropColumn('market_id');
            $table->dropColumn('delivery_address_id');
            $table->dropColumn('payment_method_id');
            $table->dropColumn('distance');
            $table->dropColumn('driver_id');
            $table->dropColumn('driver_commission_amount');
            $table->dropColumn('tax');
        });
    }
}
