<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addpptofield extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Peoples', function(Blueprint $table) { 
            $table->string('BudgetPlace')->default('');
            $table->string('BudgetStatus')->default('');
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
            $table->dropColumn('BudgetPlace');
            $table->dropColumn('BudgetStatus');
        });
    }
}
