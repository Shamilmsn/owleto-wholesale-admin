<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('market_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->integer('market_id')->unsigned();
            $table->bigInteger('payment_method_id')->unsigned();

            $table->foreign('market_id')->references('id')->on('markets');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('market_payment_methods');
    }
}
