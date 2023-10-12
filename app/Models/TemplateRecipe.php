<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TemplateRecipe extends Model {
    protected $table = 'TemplateRecipes';
    protected $primaryKey = 'TemplateRecipeID';
    public $timestamps = false;
    protected $guarded = [];
}
