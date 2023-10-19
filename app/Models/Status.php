<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Status extends Model {
    protected $table = 'Status';
    protected $primaryKey = 'StatusID';
    public $timestamps = false;
    protected $guarded = [];
}
