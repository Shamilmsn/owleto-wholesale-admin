<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameWalletIdFromUserWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_wallet_transactions', function (Blueprint $table) {
            $table->renameColumn('wallet_id', 'order_id');
            $table->integer('package_order_id')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_wallet_transactions', function (Blueprint $table) {
            $table->renameColumn('order_id', 'wallet_id');
            $table->dropColumn('package_order_id');
        });
    }
}
