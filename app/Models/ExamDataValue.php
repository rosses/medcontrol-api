<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ExamDataValue extends Model {
    protected $table = 'ExamDataValues';
    protected $primaryKey = 'ExamDataValueID';
    public $timestamps = false;
    protected $guarded = [];
}
