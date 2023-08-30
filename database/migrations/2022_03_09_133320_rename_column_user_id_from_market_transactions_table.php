<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnUserIdFromMarketTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('market_transactions', function (Blueprint $table) {
            $table->renameColumn('user_id', 'market_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('market_transactions', function (Blueprint $table) {
            $table->renameColumn('market_id', 'user_id');
        });
    }
}
