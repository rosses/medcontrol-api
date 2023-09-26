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
        Schema::create('Anthropometrys', function (Blueprint $table) {
            $table->increments('AnthropometryID');
            $table->integer('PeopleID')->nullable();
            $table->integer('DateID')->nullable();
            $table->float('Weight', 0, 0)->nullable();
            $table->float('Height', 0, 0)->nullable();
            $table->integer('Sistolic')->nullable();
            $table->integer('Diastolic')->nullable();
            $table->float('Temperature', 0, 0)->nullable();
            $table->integer('CreatedUserID')->nullable();
            $table->dateTime('CreatedAt')->nullable();

            $table->primary(['AnthropometryID'], 'PK_Anthropometrys');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Anthropometrys');
    }
};
