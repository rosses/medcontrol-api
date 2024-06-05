<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SurgerysSts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('PeopleDates', 'PeopleSurgerys');
        Schema::table('PeopleSurgerys', function (Blueprint $table) {
            $table->renameColumn('PeopleDateID','PeopleSurgeryID');
            $table->integer('SurgeryID')->default(0);
            $table->integer('DateID')->default(0);
            $table->index(['PeopleID'], 'IDX_PeopleID');
            $table->index(['DateID'], 'IDX_DateID');
        });

        Schema::table('Dates',  function (Blueprint $table) {
            $table->integer("PeopleSurgeryID")->default(0);
            $table->index(['PeopleSurgeryID'], 'IDX_PeopleSurgeryID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('PeopleDates', 'PeopleSurgerys');
        Schema::table('PeopleSurgerys', function (Blueprint $table) {
            $table->renameColumn('PeopleSurgeryID','PeopleDateID');
            $table->dropIndex('IDX_PeopleID');
            $table->dropIndex('IDX_DateID');
            $table->dropColumn('SurgeryID');
            $table->dropColumn('DateID');
        });
        Schema::table('Dates',  function (Blueprint $table) {
            $table->dropColumn("PeopleSurgeryID");
            $table->dropIndex('IDX_PeopleSurgeryID');
        });
    }
}
