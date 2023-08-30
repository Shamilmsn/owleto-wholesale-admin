<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_category')->nullable()->after('type')->comment('VENDOR_BASED, PRODUCT_BASED')->default('VENDOR_BASED');
            $table->integer('parent_id')->unsigned()->nullable()->after('order_category');
            $table->foreign('parent_id')->references('id')->on('orders')->onDelete('cascade');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['order_category', 'parent_id']);
        });
    }
}
