<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Specialist extends Model {
    protected $table = 'Specialists';
    protected $primaryKey = 'SpecialistID';
    public $timestamps = false;
    protected $guarded = [];
}
