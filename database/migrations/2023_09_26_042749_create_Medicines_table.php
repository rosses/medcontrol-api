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
        Schema::create('Medicines', function (Blueprint $table) {
            $table->increments('MedicineID');
            $table->string('Name', 128)->nullable();
            $table->integer('LabID')->nullable();
            $table->integer('Active')->default(1);

            $table->primary(['MedicineID'], 'PK_Medicine');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Medicines');
    }
};
