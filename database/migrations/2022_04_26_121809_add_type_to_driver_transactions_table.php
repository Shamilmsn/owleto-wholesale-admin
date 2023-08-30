<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToDriverTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_transactions', function (Blueprint $table) {
            $table->string('type')->comment('CREDIT, DEBIT')->after('user_id')->nullable();
            $table->longText('description')->after('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_transactions', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('description');
        });
    }
}
