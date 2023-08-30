<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->double('price_without_gst')->after('price')->nullable();
            $table->double('tcs_percentage')->after('price_without_gst')->nullable();
            $table->double('tcs_amount')->after('tcs_percentage')->nullable();
            $table->double('tds_percentage')->after('tcs_amount')->nullable();
            $table->double('tds_amount')->after('tds_percentage')->nullable();
            $table->double('eighty_percentage_of_commission_amount')
                ->after('tds_amount')->nullable();
            $table->double('vendor_payment_amount')
                ->after('eighty_percentage_of_commission_amount')->nullable();

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
            $table->dropColumn('price_without_gst');
            $table->dropColumn('tcs_percentage');
            $table->dropColumn('tcs_amount');
            $table->dropColumn('tds_percentage');
            $table->dropColumn('tds_amount');
            $table->dropColumn('eighty_percentage_of_commission_amount');
            $table->dropColumn('vendor_payment_amount');
        });
    }
}
