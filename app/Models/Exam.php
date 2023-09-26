<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model {
    protected $table = 'Exams';
    protected $primaryKey = 'ExamID';
    public $timestamps = false;
    protected $guarded = [];
}
