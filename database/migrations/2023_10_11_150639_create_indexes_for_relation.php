<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndexesForRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Evolutions', function(Blueprint $table) { $table->index('DateID'); });
        Schema::table('Interviews', function(Blueprint $table) { $table->index('DateID'); });
        Schema::table('Recipes', function(Blueprint $table) { $table->index('DateID'); });
        Schema::table('Orders', function(Blueprint $table) { $table->index('DateID'); });
        Schema::table('Certificates', function(Blueprint $table) { $table->index('DateID'); });
        Schema::table('Anthropometrys', function(Blueprint $table) { $table->index('DateID'); });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Evolutions', function (Blueprint $table) { $table->dropIndex(['DateID']); });
        Schema::table('Interviews', function (Blueprint $table) { $table->dropIndex(['DateID']); });
        Schema::table('Recipes', function (Blueprint $table) { $table->dropIndex(['DateID']); });
        Schema::table('Orders', function (Blueprint $table) { $table->dropIndex(['DateID']); });
        Schema::table('Certificates', function (Blueprint $table) { $table->dropIndex(['DateID']); });
        Schema::table('Anthropometrys', function (Blueprint $table) { $table->dropIndex(['DateID']); });
    }
}
