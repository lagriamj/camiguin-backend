<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_condition_id')->nullable();
            $table->text('vendor')->nullable();
            $table->double('weight')->nullable();
            $table->string('storage_condition')->nullable();
            $table->boolean('pre_order')->nullable();

            $table->foreign('product_condition_id')->references('id')->on('product_conditions')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_name', function (Blueprint $table) {
            $table->dropColumn(['product_condition_id', 'vendor', 'weight', 'storage_condition', 'pre_order']);
        });
    }
}
