<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableHealths extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Healths', function (Blueprint $table) {
            $table->increments('HealthID');
            $table->string('Name', 256);
            $table->integer('Active')->default('1');

            //$table->primary(['HealthID'], 'PK_Healths');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Healths');
    }
}
