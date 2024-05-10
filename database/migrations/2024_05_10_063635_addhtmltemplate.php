<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addhtmltemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('CertificateTypes', function(Blueprint $table) { 
            $table->text('templateHtml')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('CertificateTypes', function (Blueprint $table) { 
            $table->dropColumn('templateHtml'); 
        });
    }
}
