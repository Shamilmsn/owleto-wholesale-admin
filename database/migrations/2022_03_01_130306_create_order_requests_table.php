<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->integer('market_id')->unsigned();
            $table->integer('sector_id')->unsigned();
            $table->integer('type')->comment('1=>Image, 2=>text')->nullable();
            $table->longText('order_text')->nullable();
            $table->string('status')->comment('NEW,CONTACTED,ADDED TO CART,REJECTED')->nullable();
            $table->integer('order_id')->nullable();
            $table->integer('reviewed_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('market_id')->references('id')->on('markets');
            $table->foreign('sector_id')->references('id')->on('fields');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_requests');
    }
}
