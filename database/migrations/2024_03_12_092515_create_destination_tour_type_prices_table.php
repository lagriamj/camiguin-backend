<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDestinationTourTypePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('destination_tour_type_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('destination_tour_type_id')->nullable();
            $table->unsignedBigInteger('tourist_type_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('destination_tour_type_id')->references('id')->on('destination_tour_types')->onDelete('cascade');
            $table->foreign('tourist_type_id')->references('id')->on('tourist_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('destination_tour_type_prices');
    }
}
