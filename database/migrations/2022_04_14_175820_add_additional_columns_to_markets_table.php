<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalColumnsToMarketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('markets', function (Blueprint $table) {
            $table->string('insta_name')->after('available_for_delivery')->nullable();
            $table->string('location')->after('insta_name')->nullable();
            $table->longText('about')->after('location')->nullable();
            $table->integer('media_id_profile_pic')->after('about')->nullable();
            $table->integer('media_id_cover_pic')->after('media_id_profile_pic')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('markets', function (Blueprint $table) {
            $table->dropColumn('insta_name');
            $table->dropColumn('location');
            $table->dropColumn('about');
            $table->dropColumn('media_id_profile_pic');
            $table->dropColumn('media_id_cover_pic');
        });
    }
}
