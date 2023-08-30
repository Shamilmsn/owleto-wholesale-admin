<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalFieldsToPickUpVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pick_up_vehicles', function (Blueprint $table) {
            $table->double('base_distance')->after('amount_per_kilometer')->nullable();
            $table->double('additional_amount')->after('base_distance')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pick_up_vehicles', function (Blueprint $table) {
            $table->dropColumn('base_distance');
            $table->dropColumn('additional_amount');
        });
    }
}
