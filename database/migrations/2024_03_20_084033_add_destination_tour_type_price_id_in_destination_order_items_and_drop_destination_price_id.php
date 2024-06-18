<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDestinationTourTypePriceIdInDestinationOrderItemsAndDropDestinationPriceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('destination_order_items', function (Blueprint $table) {
            $table->dropColumn('destination_price_id');
            $table->unsignedBigInteger('destination_tour_type_price_id')->nullable();

            $table->foreign('destination_tour_type_price_id')->references('id')->on('destination_tour_type_prices')->onDelete('cascade');
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
            $table->dropColumn('destination_tour_type_price_id');
            $table->unsignedBigInteger('destination_price_id')->nullable();

            $table->foreign('destination_price_id')->references('id')->on('destination_prices')->onDelete('cascade');
        });
    }
}
