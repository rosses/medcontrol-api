<?php

namespace App\Http\Controllers;

use App\Models\Date; 
use App\Models\Anthropometry;
use App\Models\Certificate;
use App\Models\Evolution;
use App\Models\Interview;
use App\Models\People;
use App\Models\Order;
use App\Models\Recipe;
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
    public function getSession(Request $request) {

        try {

            $rows = Date::select(
                        "Dates.*",
                        "Users.Name as UpdatedUserName"
                    )
                    ->where("DateID", $request->id)
                    ->join("Users","Users.UserID","=","Dates.UpdatedUserID")
                    ->orderBy("Date","Desc")->get();
            
            $output = [];
            foreach ($rows as $row) {
                $output = $row;
                $output["certificates"] = Certificate::where("DateID", $row->DateID)->get();
                $output["recipes"] = Recipe::where("DateID", $row->DateID)->get();
                $output["interviews"] = Interview::where("DateID", $row->DateID)->get();
                $output["orders"] = Order::select("Orders.*","Exams.ExamTypeID")
                                ->join("Exams","Exams.ExamID","=","Orders.ExamID")
                                ->where("DateID", $row->DateID)->get();
                $output["evolutions"] = Evolution::where("DateID", $row->DateID)->get();
                
                $ap = Anthropometry::where("DateID", $row->DateID)->first();
                if (!$ap) {
                    $ap = new Anthropometry();
                    $ap->DateID = $row->DateID;
                    $ap->Weight = 0;
                    $ap->Height = 0;
                    $ap->Sistolic = 0;
                    $ap->Diastolic = 0;
                    $ap->Temperature = 0;
                    $ap->PeopleID = $row->PeopleID;
                    $ap->CreatedUserID = JWTAuth::user()->UserID;
                    $ap->CreatedAt = date("Y-m-d H:i:s");
                    $ap->save();
                }
                $output["anthropometry"] = $ap;
                
            }
            return response()->json($output);





            return response()->json([
                "success" => true 
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }

    }
    public function saveSession(Request $request) {

        try {
            throw new \Exception("Noooo");
            $date= Date::where("DateID", $request->DateID)->first();
            $date->Confirmed = 1;
            $date->AntDrugs = $request->AntDrugs;
            $date->AntMedical = $request->AntMedical;
            $date->AntAllergy = $request->AntAllergy;
            $date->AntSurgical = $request->AntSurgical;
            $date->DiagnosisID = $request->DiagnosisID;
            $date->SurgeryID = $request->SurgeryID;
            $date->Obs = $request->Obs;
            $date->UpdatedUserID = JWTAuth::user()->UserID;
            $date->UpdatedAt = date("Y-m-d H:i:s");
            $date->save();




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
