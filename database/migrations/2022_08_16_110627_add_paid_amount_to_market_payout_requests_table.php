<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaidAmountToMarketPayoutRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('market_payout_requests', function (Blueprint $table) {
            $table->double('paid_amount')->after('amount')->nullable();
            $table->integer('market_id')->after('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('market_payout_requests', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
            $table->dropColumn('market_id');
        });
    }
}
