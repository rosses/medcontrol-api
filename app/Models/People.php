<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class People extends Model {
    protected $table = 'Peoples';
    protected $primaryKey = 'PeopleID';
    public $timestamps = false;
    protected $guarded = [];
}
