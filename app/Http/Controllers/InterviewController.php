<?php

namespace App\Http\Controllers;
 
use App\Models\Interview;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class InterviewController extends Controller
{ 
    public function delete($id) {
        Interview::findOrFail($id)->delete(); //->update(["Active"=>0]);
        return response()->json([
            "success" => true
        ], 200);
    }
    public function vb($id) {
        Interview::findOrFail($id)->update([
            "VB_Check"=>1,
            "VB_As"=>date("Y-m-d H:i:s"),
            "VB_By"=>JWTAuth::user()->UserID
        ]);
        return response()->json([
            "success" => true
        ], 200);
    }
}
