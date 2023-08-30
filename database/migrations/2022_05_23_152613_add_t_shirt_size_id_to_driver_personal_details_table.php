<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTShirtSizeIdToDriverPersonalDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_personal_details', function (Blueprint $table) {
            $table->integer('t_shirt_size_id')->after('pincode')->nullable();
            $table->string('profile_image')->after('t_shirt_size_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_personal_details', function (Blueprint $table) {
            $table->dropColumn('t_shirt_size_id');
            $table->dropColumn('profile_image');
        });
    }
}
