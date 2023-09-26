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
        Schema::create('Interviews', function (Blueprint $table) {
            $table->increments('InterviewID');
            $table->integer('SpecialistID')->nullable();
            $table->text('Description')->nullable();
            $table->integer('DiagnosisID')->nullable();
            $table->text('WantText')->nullable();
            $table->integer('PeopleID')->nullable();
            $table->integer('DateID')->nullable();
            $table->integer('CreatedUserID')->nullable();
            $table->integer('CreatedAt')->nullable();

            $table->primary(['InterviewID'], 'PK_Interviews');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Interviews');
    }
};
