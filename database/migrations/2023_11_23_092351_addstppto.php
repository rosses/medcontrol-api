<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Addstppto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('BudgetStatus', function(Blueprint $table)
        {
            $table->increments('BudgetStatusID');
            $table->string('Name')->default('');
        });

        Schema::table('Peoples', function (Blueprint $table) { 
            $table->integer('BudgetStatusID')->default('0'); 
        });

        DB::table('BudgetStatus')->insert([
            ['Name' => 'Solicitado'],
            ['Name' => 'Enviado'],
            ['Name' => 'Pac. Solicita'],
            ['Name' => 'Pac. Ges'],
            ['Name' => 'Pac. Pad'],
            ['Name' => 'En Espera'],
            ['Name' => 'Hay que solicitarlo'],
            ['Name' => 'Edición'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('BudgetStatus');
        Schema::table('Peoples', function (Blueprint $table) { 
            $table->dropColumn('BudgetStatusID');
        });
    }
}
