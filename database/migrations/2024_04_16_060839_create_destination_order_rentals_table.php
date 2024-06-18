<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDestinationOrderRentalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('destination_order_rentals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('destination_order_id');
            $table->unsignedBigInteger('destination_rental_id');
            $table->unsignedBigInteger('quantity');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('destination_order_id')->references('id')->on('destination_orders')->onDelete('cascade');
            $table->foreign('destination_rental_id')->references('id')->on('destination_rentals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('destination_order_rentals');
    }
}
