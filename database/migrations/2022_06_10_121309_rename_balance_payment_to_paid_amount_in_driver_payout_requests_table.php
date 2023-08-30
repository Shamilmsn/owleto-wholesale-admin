<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameBalancePaymentToPaidAmountInDriverPayoutRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_payout_requests', function (Blueprint $table) {
            $table->renameColumn('balance_amount_to_pay','paid_amount');
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
           $table->renameColumn('paid_amount','balance_amount_to_pay');
        });
    }
}
