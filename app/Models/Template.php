<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Template extends Model {
    protected $table = 'Templates';
    protected $primaryKey = 'TemplateID';
    public $timestamps = false;
    protected $guarded = [];
}
