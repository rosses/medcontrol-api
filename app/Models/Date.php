<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Date extends Model {
    protected $table = 'Dates';
    protected $primaryKey = 'DateID';
    public $timestamps = false;
    protected $guarded = [];
}
