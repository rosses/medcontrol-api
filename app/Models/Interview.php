<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model {
    protected $table = 'Interviews';
    protected $primaryKey = 'InterviewID';
    public $timestamps = false;
    protected $guarded = [];
}
