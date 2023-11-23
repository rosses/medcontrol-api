<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addfechaeva extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Peoples', function (Blueprint $table) { 
            $table->date('DateAsEvaluation')->nullable(); 
            $table->date('DateAsSurgery')->nullable(); 
            $table->date('DateAsFinish')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Peoples', function (Blueprint $table) { 
            $table->dropColumn('DateAsEvaluation'); 
            $table->dropColumn('DateAsSurgery'); 
            $table->dropColumn('DateAsFinish'); 
        });
    }
}
