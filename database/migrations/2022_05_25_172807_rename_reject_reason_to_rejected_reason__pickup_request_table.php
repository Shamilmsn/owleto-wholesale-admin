<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameRejectReasonToRejectedReasonPickupRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pick_up_delivery_order_requests', function (Blueprint $table) {
            $table->renameColumn('reject_reason', 'rejected_reason');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pick_up_delivery_order_requests', function (Blueprint $table) {
            $table->dropColumn('rejected_reason', 'reject_reason');
        });
    }
}
