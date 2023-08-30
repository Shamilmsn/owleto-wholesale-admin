<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRazorPayFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status')->comment('PENDING, SUCCESS, FAILED')->default('PENDING')->after('market_id')->nullable();
            $table->string('payment_gateway')->after('payment_status')->nullable();
            $table->string('razorpay_order_id')->after('payment_gateway')->nullable();
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
            $table->dropColumn(['payment_status', 'payment_gateway', 'razorpay_order_id']);
        });
    }
}
