<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SurgerysSts3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('PeopleSurgerys', function (Blueprint $table) {
            $table->dateTime('DateAsEnter')->nullable();
            $table->dateTime('DateAsFinish')->nullable();
            $table->dateTime('DateAsSurgery')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('PeopleSurgerys', function (Blueprint $table) {
            $table->dropColumn('DateAsEnter');
            $table->dropColumn('DateAsFinish');
            $table->dropColumn('DateAsSurgery');
        });
    }
}
