<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBaseProductIdToProductAttributeOptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_attribute_options', function (Blueprint $table) {
            $table->integer('base_product_id')->unsigned()->after('attribute_option_id')->nullable();
            $table->foreign('base_product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_attribute_options', function (Blueprint $table) {
            $table->dropColumn('base_product_id')->unsigned()->nullable();
        });
    }
}
