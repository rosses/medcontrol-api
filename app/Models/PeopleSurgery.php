<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PeopleSurgery extends Model {
    protected $table = 'PeopleSurgerys';
    protected $primaryKey = 'PeopleSurgeryID';
    public $timestamps = false;
    protected $guarded = [];
}
