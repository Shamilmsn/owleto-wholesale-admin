<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->double('minimum_order_value')->after('expires_at')->nullable();
            $table->integer('use_limit_per_person')->after('expires_at')->nullable();
            $table->integer('total_number_of_coupon')->after('expires_at')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('minimum_order_value');
            $table->dropColumn('use_limit_per_person');
            $table->dropColumn('total_number_of_coupon');

        });
    }
}
