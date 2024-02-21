<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GroupSingle extends Model {
    protected $table = 'GroupSingles';
    protected $primaryKey = 'GroupSingleID';
    public $timestamps = false;
    protected $guarded = [];
}
