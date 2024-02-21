<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupsdata extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('GroupSingles', function (Blueprint $table) {
            $table->increments('GroupSingleID');
            $table->string('Type', 20)->default('');
            $table->integer('PeopleID')->default(0);
            $table->integer('CreatedUserID')->nullable();
            $table->dateTime('CreatedAt')->nullable();
        });
        Schema::table('GroupSingles', function(Blueprint $table) { $table->index('PeopleID'); });
        Schema::table('Recipes', function(Blueprint $table) { 
            $table->integer('GroupSingleID')->default(0);
        });
        Schema::table('Orders', function(Blueprint $table) { 
            $table->integer('GroupSingleID')->default(0);
        });
        Schema::table('Recipes', function(Blueprint $table) { $table->index('GroupSingleID'); });
        Schema::table('Orders', function(Blueprint $table) { $table->index('GroupSingleID'); });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('GroupSingles');
        Schema::table('Recipes', function(Blueprint $table) { 
            $table->dropIndex('GroupSingleID');
            $table->dropColumn('GroupSingleID');
        });
        Schema::table('Orders', function(Blueprint $table) { 
            $table->dropIndex('GroupSingleID');
            $table->dropColumn('GroupSingleID');
        });
    }
}
