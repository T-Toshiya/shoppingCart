<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderAmazonDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_amazon_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('orderNum');
            $table->bigInteger('productId');
            $table->text('productName');
            $table->integer('productPrice');
            $table->text('imagePath');
            $table->integer('orderQuantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_amazon_details');
    }
}
