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
        Schema::create('Exams', function (Blueprint $table) {
            $table->increments('ExamID');
            $table->integer('ExamTypeID')->nullable();
            $table->string('Name', 128)->nullable();
            $table->integer('Active')->default(1);

            $table->primary(['ExamID'], 'PK_Exams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Exams');
    }
};
