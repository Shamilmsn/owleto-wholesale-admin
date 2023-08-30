<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('package_id')->nullable();
            $table->double('package_price')->nullable();
            $table->double('price_per_delivery')->nullable();
            $table->double('commission_percentage')->nullable();
            $table->double('commission_amount')->nullable();
            $table->dateTime('date')->nullable();
            $table->integer('day_id')->nullable();
            $table->integer('delivery_time_id')->nullable();
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
        Schema::dropIfExists('package_orders');
    }
}
