<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDestinationRentalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('destination_rentals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rental_id')->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('rental_id')->references('id')->on('rentals')->onDelete('cascade');
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('destination_rentals');
    }
}