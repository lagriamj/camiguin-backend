<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDestinationOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('destination_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orders_id')->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->unsignedBigInteger('destination_price_id')->nullable();
            $table->bigInteger('quantity')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('orders_id')->references('id')->on('destination_orders')->onDelete('cascade');
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
            $table->foreign('destination_price_id')->references('id')->on('destination_prices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('destination_order_items');
    }
}
