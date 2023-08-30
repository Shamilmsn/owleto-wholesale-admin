<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBalanceAmountToPayInDriverPayoutRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_payout_requests', function (Blueprint $table) {
           $table->double('balance_amount_to_pay')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_payout_requests', function (Blueprint $table) {
            $table->dropColumn('balance_amount_to_pay');
        });
    }
}
