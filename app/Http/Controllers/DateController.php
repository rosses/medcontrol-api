<?php

namespace App\Http\Controllers;

use App\Models\Date; 
use App\Models\Anthropometry; 
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;


class DateController extends Controller
{
    public function next() {
        $rows = Date::select('Dates.*', 'Peoples.CardCode', 'Peoples.Name', 'Peoples.Lastname', 'Peoples.Lastname2', 'Groups.Name as GroupName')
                ->join('Peoples', 'Peoples.PeopleID','=','Dates.PeopleID')
                ->join('Groups', 'Groups.GroupID','=','Peoples.GroupID')
                ->where('Date', '>=', date("Y-m-d"))
                ->orderBy('Dates.Date','ASC')
                ->orderBy('Dates.Time','ASC')
                ->get();
        return response()->json($rows);
    }
    public function confirm(Request $request) {

        try {
            
            $date= Date::where("DateID", $request->DateID)->first();
            $date->Confirmed = 1;
            $date->save();

            // Make Anthropometry
            $anthropometry = new Anthropometry();
            $anthropometry->PeopleID = $date->PeopleID;
            $anthropometry->DateID = $date->DateID;
            $anthropometry->Weight = $request->Weight;
            $anthropometry->Height = $request->Height;
            $anthropometry->Temperature = $request->Temperature;
            $anthropometry->Sistolic = $request->Sistolic;
            $anthropometry->Diastolic = $request->Diastolic;
            $anthropometry->CreatedUserID = JWTAuth::user()->UserID;
            $anthropometry->CreatedAt = date("Y-m-d H:i:s");
            $anthropometry->save();

            return response()->json([
                "success" => true 
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }
       
    }
    
}
