<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addstatusfields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Peoples', function(Blueprint $table) { 
            $table->integer('StatusID')->after('GroupID')->default('1');
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
            $table->dropColumn('StatusID');
        });
    }
}
