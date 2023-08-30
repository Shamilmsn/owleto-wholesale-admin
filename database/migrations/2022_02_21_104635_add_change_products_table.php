<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChangeProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {

            $table->string('variant_name')->after('name')->nullable();
            $table->integer('sector_id')->after('category_id')->unsigned()->nullable();
            $table->integer('parent_id')->after('category_id')->unsigned()->nullable();
            $table->integer('product_type')->after('category_id');
            $table->boolean('is_base_product')->after('category_id')->default(0);
            $table->integer('stock')->after('category_id');
            $table->integer('review_count')->after('category_id')->nullable();
            $table->integer('avg_rating')->after('category_id')->nullable();
            $table->string('sku')->after('category_id');
            $table->foreign('sector_id')->references('id')->on('fields');
            $table->foreign('parent_id')->references('id')->on('products');

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
            $table->dropColumn('variant_name')->after('name')->nullable();
            $table->dropColumn('sector_id')->after('category_id')->unsigned();
            $table->dropColumn('parent_id')->after('category_id')->unsigned();
            $table->dropColumn('product_type')->after('category_id');
            $table->dropColumn('is_base_product')->after('category_id')->default(0);
            $table->dropColumn('stock')->after('category_id');
            $table->dropColumn('review_count')->after('category_id')->nullable();
            $table->dropColumn('avg_rating')->after('category_id')->nullable();
            $table->dropColumn('sku')->after('category_id');
        });
    }
}
