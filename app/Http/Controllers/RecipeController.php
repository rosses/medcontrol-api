<?php

namespace App\Http\Controllers;
 
use App\Models\Recipe;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class RecipeController extends Controller
{ 
    public function delete($id) {
        Recipe::findOrFail($id)->delete(); //->update(["Active"=>0]);
        return response()->json([
            "success" => true
        ], 200);
    }
}
