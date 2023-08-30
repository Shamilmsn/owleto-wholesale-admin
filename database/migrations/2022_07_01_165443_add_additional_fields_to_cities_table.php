<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalFieldsToCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->double('center_latitude')->after('name')->nullable();
            $table->double('center_longitude')->after('center_latitude')->nullable();
            $table->double('radius')->after('center_longitude')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('center_latitude');
            $table->dropColumn('center_longitude');
            $table->dropColumn('radius');
        });
    }
}
