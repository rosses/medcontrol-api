<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndexesv3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Exams', function(Blueprint $table) { $table->index('ExamTypeID'); });
        Schema::table('Anthropometrys', function(Blueprint $table) { $table->index('PeopleID'); });
        Schema::table('Recipes', function(Blueprint $table) { $table->index('PeopleID'); });
        Schema::table('Certificates', function(Blueprint $table) { $table->index('PeopleID'); });
        Schema::table('Orders', function(Blueprint $table) { 
            $table->index('PeopleID'); 
            $table->index('ExamID'); 
        });
        Schema::table('Evolutions', function(Blueprint $table) { $table->index('PeopleID'); });
        Schema::table('Interviews', function(Blueprint $table) { $table->index('PeopleID'); });
        Schema::table('Peoples', function(Blueprint $table) { $table->index('GroupID'); });
        Schema::table('Dates', function(Blueprint $table) { 
            $table->date('StatusID')->after('DestinationGroupID')->nullable();
            $table->index('StatusID'); 
            $table->index('PeopleID'); 
            $table->index('SurgeryID'); 
            $table->index('UpdatedUserID'); 
        });
        Schema::create('Status', function (Blueprint $table) {
            $table->increments('StatusID');
            $table->integer('GroupID')->default(0);
            $table->string('Name')->default('');
            $table->integer('Active')->default('1');
            $table->index('GroupID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Exams', function (Blueprint $table) { $table->dropIndex(['ExamTypeID']); });
        Schema::table('Anthropometrys', function (Blueprint $table) { $table->dropIndex(['PeopleID']); });
        Schema::table('Recipes', function (Blueprint $table) { $table->dropIndex(['PeopleID']); });
        Schema::table('Certificates', function (Blueprint $table) { $table->dropIndex(['PeopleID']); });
        Schema::table('Peoples', function (Blueprint $table) { $table->dropIndex(['GroupID']); });
        Schema::table('Orders', function (Blueprint $table) { 
            $table->dropIndex(['PeopleID']); 
            $table->dropIndex(['ExamID']); 
        });
        Schema::table('Evolutions', function (Blueprint $table) { $table->dropIndex(['PeopleID']); });
        Schema::table('Interviews', function (Blueprint $table) { $table->dropIndex(['PeopleID']); });
        Schema::table('Dates', function (Blueprint $table) { 
            $table->dropColumn('StatusID');
            $table->dropIndex(['PeopleID']); 
            $table->dropIndex(['SurgeryID']); 
            $table->dropIndex(['UpdatedUserID']); 
        });
        Schema::dropIfExists('Status');
    }
}
