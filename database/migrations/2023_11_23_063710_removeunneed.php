<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Removeunneed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Dates', function (Blueprint $table) { 
            $table->dropColumn('CreatedGroupID'); 
            $table->dropColumn('DestinationGroupID'); 
            $table->dropColumn('StatusID'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ExamDatas', function(Blueprint $table) { 
            $table->integer('CreatedGroupID')->nullable();
            $table->integer('DestinationGroupID')->nullable();
            $table->integer('StatusID')->after('DestinationGroupID')->default('0');
            $table->index('StatusID'); 
        });
    }
}
