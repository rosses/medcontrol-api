<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TemplateCertificate extends Model {
    protected $table = 'TemplateCertificates';
    protected $primaryKey = 'TemplateCertificateID';
    public $timestamps = false;
    protected $guarded = [];
}
