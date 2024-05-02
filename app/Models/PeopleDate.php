<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PeopleDate extends Model {
    protected $table = 'PeopleDates';
    protected $primaryKey = 'PeopleDateID';
    public $timestamps = false;
    protected $guarded = [];
}
