<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Health extends Model {
    protected $table = 'Healths';
    protected $primaryKey = 'HealthID';
    public $timestamps = false;
    protected $guarded = [];
}
