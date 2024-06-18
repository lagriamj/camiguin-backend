<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropQuantityInDestinationOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('destination_order_items', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->unsignedBigInteger('destination_tour_type_id')->nullable();
            $table->boolean('used')->nullable()->default(false);

            $table->foreign('destination_tour_type_id')->references('id')->on('destination_tour_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('destination_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('quantity')->nullable();
            $table->dropColumn('used');
            $table->dropColumn('destination_tour_type_id');
        });
    }
}
