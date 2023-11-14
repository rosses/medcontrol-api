<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addexamdatatype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ExamDatas', function(Blueprint $table) { 
            $table->string('ExamDataType')->default('text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ExamDatas', function (Blueprint $table) { 
            $table->dropColumn('ExamDataType'); 
        });
    }
}
