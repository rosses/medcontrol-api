<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Evolution extends Model {
    protected $table = 'Evolutions';
    protected $primaryKey = 'EvolutionID';
    public $timestamps = false;
    protected $guarded = [];
}
