<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model {
    protected $table = 'Certificates';
    protected $primaryKey = 'CertificateID';
    public $timestamps = false;
    protected $guarded = [];
}
