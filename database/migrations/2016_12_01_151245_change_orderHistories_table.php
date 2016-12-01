<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeOrderHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_histories', function (Blueprint $table) {
            $table->dropColumn('userName');
            $table->dropColumn('productName');
            $table->dropColumn('totalNum');
            $table->dropColumn('totalMoney');
            $table->dropColumn('imagePath');
            $table->integer('userId')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_histories', function (Blueprint $table) {
            //
            $table->dropColumn('orderNum');
            $table->dropColumn('userId');
            $table->string('userName');
            $table->text('productName');
            $table->integer('totalNum');
            $table->integer('totalMoney');
            $table->text('imagePath');
        });
    }
}
