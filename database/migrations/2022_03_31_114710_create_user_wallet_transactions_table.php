<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('wallet_id')->nullable();
            $table->string('type')->comment('DEBIT, CREDIT')->nullable();
            $table->double('amount')->nullable();
            $table->double('balance')->nullable();
            $table->longText('description')->nullable();
            $table->dateTime('cancelled_date')->nullable();
            $table->integer('package_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_wallet_transactions');
    }
}
