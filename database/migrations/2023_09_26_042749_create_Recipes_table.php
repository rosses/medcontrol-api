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
        Schema::create('Recipes', function (Blueprint $table) {
            $table->increments('RecipeID');
            $table->integer('MedicineID')->nullable();
            $table->string('Dose', 128)->nullable();
            $table->string('Period', 128)->nullable();
            $table->string('Periodicity', 128)->nullable();
            $table->integer('PeopleID')->nullable();
            $table->integer('DateID')->nullable();
            $table->integer('CreatedUserID')->nullable();
            $table->dateTime('CreatedAt')->nullable();

            $table->primary(['RecipeID'], 'PK_Recipes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Recipes');
    }
};
