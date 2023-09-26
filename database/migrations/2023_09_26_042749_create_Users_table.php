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
        Schema::create('Users', function (Blueprint $table) {
            $table->increments('UserID');
            $table->string('Name', 256);
            $table->string('Pswd', 64);
            $table->string('Email', 256);
            $table->string('RecoverToken', 128)->default('');
            $table->integer('ProfileID');
            $table->integer('Active')->default('1');

            $table->primary(['UserID'], 'PK_Users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Users');
    }
};
