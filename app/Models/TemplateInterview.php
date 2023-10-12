<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TemplateInterview extends Model {
    protected $table = 'TemplateInterviews';
    protected $primaryKey = 'TemplateInterviewID';
    public $timestamps = false;
    protected $guarded = [];
}
