<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ExamType extends Model {
    protected $table = 'ExamTypes';
    protected $primaryKey = 'ExamTypeID';
    public $timestamps = false;
    protected $guarded = [];
}
