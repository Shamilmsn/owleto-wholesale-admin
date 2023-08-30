<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDayIdToPackageDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_days', function (Blueprint $table) {
            $table->integer('day_id')->references('id')->on('days')->after('package_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_days', function (Blueprint $table) {
            $table->dropColumn('day_id');
        });
    }
}
