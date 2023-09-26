<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Surgery extends Model {
    protected $table = 'Surgerys';
    protected $primaryKey = 'SurgeryID';
    public $timestamps = false;
    protected $guarded = [];
}
