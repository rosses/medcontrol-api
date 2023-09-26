<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model {
    protected $table = 'Recipes';
    protected $primaryKey = 'RecipeID';
    public $timestamps = false;
    protected $guarded = [];
}
