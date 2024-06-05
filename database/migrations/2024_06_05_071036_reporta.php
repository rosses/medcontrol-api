<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Reporta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('PosopReport', function (Blueprint $table) {
            $table->integer('PeopleID')->default(0);
            $table->string('RUT')->default('');
            $table->string('Nombre')->default('');
            $table->string('Prevision')->default('');
            $table->string('Estado')->default('');
            $table->string('Cirugia')->default('');
            $table->datetime('FechaIngreso')->nullable();
            $table->datetime('FechaTermino')->nullable();
            $table->datetime('FechaCirugia')->nullable();
            $table->float('IMC')->default(0);
            $table->string('Nutriologo')->default('');
            $table->string('Psicologo')->default('');
            $table->string('Nutricionista')->default('');
            $table->string('Psiquiatra')->default('');
            $table->integer('Lab')->default(0);
            $table->integer('RxTx')->default(0);
            $table->integer('Eco')->default(0);
            $table->integer('ECG')->default(0);
            $table->integer('Eco2')->default(0);
            $table->integer('EDA')->default(0);
            
            $table->integer('CreatedUserID')->default(0);
            $table->dateTime('CreatedAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("PosopReport");
    }
}
