<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToDeliveryAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_addresses', function (Blueprint $table) {
            $table->string('house_number')->nullable()->after('longitude');
            $table->string('area')->nullable()->after('house_number');
            $table->string('direction_to_reach')->nullable()->after('area');
            $table->integer('pincode')->nullable()->after('direction_to_reach');
            $table->integer('address_as')->nullable()->after('pincode')->comment('1 => Home, 2 => Work, 3 => Other');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_addresses', function (Blueprint $table) {
            $table->dropColumn('house_number');
            $table->dropColumn('area');
            $table->dropColumn('direction_to_reach');
            $table->dropColumn('pincode');
            $table->dropColumn('address_as');
        });
    }
}
