<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFlashSaleDetailToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dateTime('flash_sale_start_time')->nullable()->after('owleto_commission_percentage');
            $table->dateTime('flash_sale_end_time')->nullable()->after('owleto_commission_percentage');
            $table->double('flash_sale_price')->nullable()->after('discount_price');
            $table->boolean('is_flash_sale_approved')->nullable()->after('owleto_commission_percentage')->default('0');
            $table->boolean('is_approved')->nullable()->after('owleto_commission_percentage')->default('0');
            $table->string('food_type')->after('owleto_commission_percentage')->comment('VEG','NONVEG')->nullable();

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
            $table->dropColumn('flash_sale_start_time')->nullable();
            $table->dropColumn('flash_sale_end_time')->nullable();
            $table->dropColumn('flash_sale_price')->nullable();
            $table->dropColumn('is_flash_sale_approved')->nullable();
            $table->dropColumn('is_approved')->nullable();
            $table->dropColumn('food_type');
        });
    }
}
