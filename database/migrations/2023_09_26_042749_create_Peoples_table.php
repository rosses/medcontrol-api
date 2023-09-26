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
        Schema::create('Peoples', function (Blueprint $table) {
            $table->increments('PeopleID');
            $table->string('CardCode', 128)->nullable()->index('IX_People');
            $table->string('Name', 128)->nullable();
            $table->string('Lastname', 128)->nullable();
            $table->string('Lastname2', 128)->nullable();
            $table->string('Email', 128)->nullable();
            $table->string('Phone', 32)->nullable();
            $table->string('Phone2', 32)->nullable();
            $table->date('Birthday')->nullable();
            $table->string('Address', 256)->nullable();
            $table->string('County', 128)->nullable();
            $table->string('City', 128)->nullable();
            $table->integer('HealthID')->nullable();
            $table->string('Profession', 128)->nullable();
            $table->text('Obs')->nullable();
            $table->integer('GroupID')->nullable();
            $table->integer('CreatedUserID')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->integer('UpdatedUserID')->nullable();
            $table->dateTime('UpdatedAt')->nullable();

            $table->primary(['PeopleID'], 'PK_People');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Peoples');
    }
};
