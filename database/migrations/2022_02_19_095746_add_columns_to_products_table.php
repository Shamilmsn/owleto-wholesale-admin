<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('is_enabled')->after('category_id')->nullable();
            $table->integer('scheduled_delivery')->after('is_enabled')->nullable();
            $table->string('order_start_time')->after('scheduled_delivery')->nullable();
            $table->string('order_end_time')->after('order_start_time')->nullable();
            $table->integer('delivery_time_id')->after('order_end_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_enabled', 'scheduled_delivery', 'order_start_time', 'order_start_time', 'delivery_time_id']);
        });
    }
}
