<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Templates', function (Blueprint $table) {
            $table->increments('TemplateID');
            $table->integer('SurgeryID')->default(0);
            $table->index('SurgeryID');
            $table->integer('CreatedUserID')->nullable();
            $table->dateTime('CreatedAt')->nullable();            
        });

        Schema::create('TemplateInterviews', function (Blueprint $table) {
            $table->increments('TemplateInterviewID');
            $table->integer('TemplateID')->default(0);
            $table->integer('SpecialistID')->nullable();
            $table->text('Description')->nullable();
            $table->integer('DiagnosisID')->nullable();
            $table->text('WantText')->nullable();
        });
        Schema::create('TemplateRecipes', function (Blueprint $table) {
            $table->increments('TemplateRecipeID');
            $table->integer('TemplateID')->default(0);
            $table->integer('MedicineID')->nullable();
            $table->string('Dose', 128)->nullable();
            $table->string('Period', 128)->nullable();
            $table->string('Periodicity', 128)->nullable();
        });
        Schema::create('TemplateOrders', function (Blueprint $table) {
            $table->increments('TemplateOrderID');
            $table->integer('TemplateID')->default(0);
            $table->integer('ExamID')->nullable();
            $table->integer('ExamTypeID')->default(0);
            $table->text('Description')->nullable();
        });
        Schema::create('TemplateCertificates', function (Blueprint $table) {
            $table->increments('TemplateCertificateID');
            $table->integer('TemplateID')->default(0);
            $table->integer('CertificateTypeID')->nullable();
            $table->text('Description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Templates');
        Schema::dropIfExists('TemplateInterviews');
        Schema::dropIfExists('TemplateRecipes');
        Schema::dropIfExists('TemplateOrders');
        Schema::dropIfExists('TemplateCertificates');
    }
}
