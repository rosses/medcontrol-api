<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ExamData extends Model {
    protected $table = 'ExamDatas';
    protected $primaryKey = 'ExamDataID';
    public $timestamps = false;
    protected $guarded = [];
}
