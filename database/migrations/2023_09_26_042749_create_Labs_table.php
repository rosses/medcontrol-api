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
        Schema::create('Labs', function (Blueprint $table) {
            $table->increments('LabID');
            $table->char('Name', 10)->nullable();
            $table->integer('Active')->default(1);

            $table->primary(['LabID'], 'PK_Labs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Labs');
    }
};
