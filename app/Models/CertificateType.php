<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CertificateType extends Model {
    protected $table = 'CertificateTypes';
    protected $primaryKey = 'CertificateTypeID';
    public $timestamps = false;
    protected $guarded = [];
}
