<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamdatavalues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ExamDataValues', function (Blueprint $table) {
            $table->increments('ExamDataValueID');
            $table->integer('OrderID')->default(0);
            $table->integer('DateID')->default(0);
            $table->integer('ExamDataID')->default(0);
            $table->string('Value', 4000)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ExamDataValues');
    }
}
