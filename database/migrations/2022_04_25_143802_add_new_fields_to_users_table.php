<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('driver_checkin_checkout_type')->after('remember_token')->nullable();
            $table->double('driver_checkin_checkout_latitude')->after('driver_checkin_checkout_type')->nullable();
            $table->double('driver_checkin_checkout_longitude')->after('driver_checkin_checkout_latitude')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
           $table->dropColumn('driver_checkin_checkout_type');
           $table->dropColumn('driver_checkin_checkout_latitude');
           $table->dropColumn('driver_checkin_checkout_longitude');
        });
    }
}
