<?php

namespace App\Http\Controllers;

use App\Models\Evolution;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class EvolutionController extends Controller
{
    public function create(Request $request) {
        $ev = new Evolution();
        $ev->Description = $request->Description;
        $ev->DateAs = $request->DateAs;
        $ev->PeopleID = $request->PeopleID;
        $ev->DateID = 0;
        $ev->CreatedUserID = JWTAuth::user()->UserID;
        $ev->CreatedAt = date("Y-m-d H:i:s");
        $ev->save();
        return response()->json($ev, 201);
    } 
    public function delete($id) {
        Evolution::findOrFail($id)->delete(); //->update(["Active"=>0]);
        return response()->json([
            "success" => true
        ], 200);
    }
}
