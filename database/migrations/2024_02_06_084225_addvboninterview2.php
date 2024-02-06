<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addvboninterview2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Interviews', function (Blueprint $table) { 
            $table->integer('VB_Check')->default(0); 
            $table->datetime('VB_As')->nullable(); 
            $table->integer('VB_By')->default(0); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Interviews', function (Blueprint $table) { 
            $table->dropColumn('VB_Check'); 
            $table->dropColumn('VB_As'); 
            $table->dropColumn('VB_By'); 
        });
    }
}
