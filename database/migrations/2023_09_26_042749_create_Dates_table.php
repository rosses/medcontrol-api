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
        Schema::create('Dates', function (Blueprint $table) {
            $table->increments('DateID');
            $table->integer('PeopleID')->nullable();
            $table->date('Date')->nullable();
            $table->time('Time', 5)->nullable();
            $table->text('Obs')->nullable();
            $table->integer('CreatedGroupID')->nullable();
            $table->integer('DestinationGroupID')->nullable();
            $table->integer('DiagnosisID')->nullable();
            $table->integer('SurgeryID')->nullable();
            $table->text('SurgeryObs')->nullable();
            $table->integer('Confirmed')->default(0);
            $table->text('AntDrugs')->nullable();
            $table->text('AntAllergy')->nullable();
            $table->text('AntSurgical')->nullable();
            $table->integer('CreatedUserID')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->integer('UpdatedUserID')->nullable();
            $table->dateTime('UpdatedAt')->nullable();

            $table->primary(['DateID'], 'PK_Dates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Dates');
    }
};
