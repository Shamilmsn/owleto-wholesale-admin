<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->double('owleto_commission_amount')->after('payment_id')->nullable();
            $table->double('driver_commission_percentage')->after('owleto_commission_amount')->nullable();
            $table->double('driver_commission_amount')->after('driver_commission_percentage')->nullable();
            $table->double('total_amount')->after('driver_commission_amount')->nullable();
            $table->double('market_balance')->after('total_amount')->nullable();
            $table->integer('market_id')->after('market_balance')->nullable();
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
            $table->dropColumn(['owleto_commission_amount',
                'driver_commission_percentage',
                'driver_commission_amount', 'total_amount',
                'market_balance', 'market_id']);
        });
    }
}
