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
        Schema::create('Evolutions', function (Blueprint $table) {
            $table->increments('EvolutionID');
            $table->text('Description')->nullable();
            $table->integer('CreatedUserID')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->integer('PeopleID')->nullable();
            $table->integer('DateID')->nullable();

            $table->primary(['EvolutionID'], 'PK_Evolutions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Evolutions');
    }
};
