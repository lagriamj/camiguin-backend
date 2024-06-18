<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCheckInInDestinationOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('destination_orders', function (Blueprint $table) {
            $table->boolean('check-in')->nullable()->default(false);
            $table->string('order_type')->nullable();
            $table->unsignedBigInteger('kiosk_id')->nullable();

            $table->foreign('kiosk_id')->references('id')->on('kiosks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('destination_orders', function (Blueprint $table) {
            $table->dropColumn('check-in');
            $table->dropColumn('order_type');
            $table->dropColumn('kiosk_id');
        });
    }
}
