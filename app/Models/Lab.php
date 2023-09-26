<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Lab extends Model {
    protected $table = 'Labs';
    protected $primaryKey = 'LabID';
    public $timestamps = false;
    protected $guarded = [];
}
