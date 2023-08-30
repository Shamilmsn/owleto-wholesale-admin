<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraColumnsToDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->integer('city_id')->after('available')->nullable();
            $table->integer('circle_id')->after('city_id')->nullable();
            $table->integer('vehicle_id')->after('circle_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('city_id');
            $table->dropColumn('circle_id');
            $table->dropColumn('vehicle_id');
        });
    }
}
