<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRefToGs4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ExamTypes', function (Blueprint $table) {
            $table->string('Side')->default('A');
            $table->integer('SideOrder')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ExamTypes', function (Blueprint $table) {
            $table->dropColumn('Side');
            $table->dropColumn('SideOrder');
        });
    }
}
