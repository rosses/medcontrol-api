<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CertificateTypes', function (Blueprint $table) {
            $table->increments('CertificateTypeID');
            $table->string('Name', 128)->nullable();
            $table->integer('Active')->default(1);

            $table->primary(['CertificateTypeID'], 'PK_CertificateTypes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('CertificateTypes');
    }
};
