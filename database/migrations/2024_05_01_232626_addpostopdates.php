<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addpostopdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('PeopleDates', function (Blueprint $table) {
            $table->increments('PeopleDateID');
            $table->integer('PeopleID');
            $table->date('DatePost1')->nullable();
            $table->date('DatePost2')->nullable();
            $table->date('DatePost3')->nullable();
            $table->date('DatePost4')->nullable();
            $table->date('DatePost5')->nullable();
            $table->date('DatePost6')->nullable();
            $table->string('DateMsg1')->default('');
            $table->string('DateMsg2')->default('');
            $table->string('DateMsg3')->default('');
            $table->string('DateMsg4')->default('');
            $table->string('DateMsg5')->default('');
            $table->string('DateMsg6')->default('');
            $table->integer('CreatedUserID')->nullable();
            $table->dateTime('CreatedAt')->nullable();   
            $table->integer('UpdatedUserID')->nullable();
            $table->dateTime('UpdatedAt')->nullable();   
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('PeopleDates');
    }
}
