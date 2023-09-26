<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Anthropometry extends Model {
    protected $table = 'Anthropometrys';
    protected $primaryKey = 'AnthropometryID';
    public $timestamps = false;
    protected $guarded = [];
}
