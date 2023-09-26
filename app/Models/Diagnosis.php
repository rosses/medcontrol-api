<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model {
    protected $table = 'Diagnosis';
    protected $primaryKey = 'DiagnosisID';
    public $timestamps = false;
    protected $guarded = [];
}
