<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Group extends Model {
    protected $table = 'Groups';
    protected $primaryKey = 'GroupID';
    public $timestamps = false;
    protected $guarded = [];
}
