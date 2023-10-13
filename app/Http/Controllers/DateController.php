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
            
            $date = Date::where("DateID", $request->DateID)->first();
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

            if ($request->anthropometry && is_array($request->anthropometry)) {
                $o = $request->anthropometry;
                $ant = new Anthropometry();
                if (isset($o["AnthropometryID"]) && $o["AnthropometryID"]!=0) {
                    $ant = Anthropometry::find($o["AnthropometryID"]);
                } else {
                    $ant->CreatedUserID = JWTAuth::user()->UserID;
                    $ant->CreatedAt = date("Y-m-d H:i:s");
                }
                $ant->Weight = $o["Weight"];
                $ant->Height = $o["Height"];
                $ant->Diastolic = $o["Diastolic"];
                $ant->Temperature = $o["Temperature"];
                $ant->Diastolic = $o["Diastolic"];
                $ant->Sistolic = $o["Sistolic"];
                $ant->DateID = $date->DateID;
                $ant->PeopleID = $date->PeopleID;
                $ant->save();
            }
            if ($request->recipes && is_array($request->recipes)) {
                foreach ($request->recipes as $r) {
                    $recipe = new Recipe();
                    if (isset($r["RecipeID"]) && $r["RecipeID"]!=0) {
                        $recipe = Recipe::find($r["RecipeID"]);
                    } else {
                        $recipe->CreatedUserID = JWTAuth::user()->UserID;
                        $recipe->CreatedAt = date("Y-m-d H:i:s");
                    }
                    $recipe->MedicineID = $r["MedicineID"];
                    $recipe->Dose = $r["Dose"];
                    $recipe->Period = $r["Period"];
                    $recipe->DateID = $date->DateID;
                    $recipe->PeopleID = $date->PeopleID;
                    $recipe->save();
                }
            }

            if ($request->interviews && is_array($request->interviews)) {
                foreach ($request->interviews as $r) {
                    $interview = new Interview();
                    if (isset($r["InterviewID"]) && $r["InterviewID"]!=0) {
                        $interview = Interview::find($r["InterviewID"]);
                    } else {
                        $interview->CreatedUserID = JWTAuth::user()->UserID;
                        $interview->CreatedAt = date("Y-m-d H:i:s");
                    }
                    $interview->SpecialistID = $r["SpecialistID"];
                    $interview->Description = $r["Description"];
                    $interview->DiagnosisID = $r["DiagnosisID"];
                    $interview->WantText = $r["WantText"];
                    $interview->DateID = $date->DateID;
                    $interview->PeopleID = $date->PeopleID;
                    $interview->save();
                }
            }

            if ($request->certificates && is_array($request->certificates)) {
                foreach ($request->certificates as $r) {
                    $certificate = new Certificate();
                    if (isset($r["CertificateID"]) && $r["CertificateID"]!=0) {
                        $certificate = Certificate::find($r["CertificateID"]);
                    } else {
                        $certificate->CreatedUserID = JWTAuth::user()->UserID;
                        $certificate->CreatedAt = date("Y-m-d H:i:s");
                    }
                    $certificate->CertificateTypeID = $r["CertificateTypeID"];
                    $certificate->Description = $r["Description"];
                    $certificate->DateID = $date->DateID;
                    $certificate->PeopleID = $date->PeopleID;
                    $certificate->save();
                }
            }

            if ($request->orders && is_array($request->orders)) {
                foreach ($request->orders as $r) {
                    $order = new Order();
                    if (isset($r["OrderID"]) && $r["OrderID"]!=0) {
                        $order = Order::find($r["OrderID"]);
                    } else {
                        $order->CreatedUserID = JWTAuth::user()->UserID;
                        $order->CreatedAt = date("Y-m-d H:i:s");
                    }
                    //$order->ExamTypeID = $r["ExamTypeID"];
                    $order->ExamID = $r["ExamID"];
                    $order->Description = $r["Description"];
                    $order->DateID = $date->DateID;
                    $order->PeopleID = $date->PeopleID;
                    $order->save();
                }
                
            } 

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
