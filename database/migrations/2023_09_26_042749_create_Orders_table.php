<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Orders', function (Blueprint $table) {
            $table->increments('OrderID');
            $table->integer('ExamID')->nullable();
            $table->integer('PeopleID')->nullable();
            $table->integer('DateID')->nullable();
            $table->text('Description')->nullable();
            $table->integer('CreatedUserID')->nullable();
            $table->dateTime('CreatedAt')->nullable();

            $table->primary(['OrderID'], 'PK_Orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Orders');
    }
};
