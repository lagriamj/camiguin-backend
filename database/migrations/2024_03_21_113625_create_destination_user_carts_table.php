<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDestinationUserCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('destination_user_carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('individuals_id')->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->unsignedBigInteger('destination_tour_type_id')->nullable();
            $table->unsignedBigInteger('destination_tour_type_price_id')->nullable();
            $table->unsignedBigInteger('quantity')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
            $table->foreign('destination_tour_type_id')->references('id')->on('destination_tour_types')->onDelete('cascade');
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
        Schema::dropIfExists('destination_user_carts');
    }
}
