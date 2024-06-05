<?php

namespace App\Http\Controllers;

use App\Models\Date; 
use App\Models\Anthropometry;
use App\Models\Certificate;
use App\Models\Diagnosis;
use App\Models\Evolution;
use App\Models\ExamType;
use App\Models\Interview;
use App\Models\Medicine;
use App\Models\People;
use App\Models\Order;
use App\Models\PeopleSurgery;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;


class DateController extends Controller
{
    public function next() {
        $rows = Date::select('Dates.*', 'Peoples.CardCode', 'Peoples.Name', 'Peoples.Lastname', 'Peoples.Lastname2', 'Groups.Name as GroupName','Status.Name as StatusName')
                ->join('Peoples', 'Peoples.PeopleID','=','Dates.PeopleID')
                ->join('Groups', 'Groups.GroupID','=','Peoples.GroupID')
                ->leftJoin('Status', 'Status.StatusID','=','Peoples.StatusID')
                ->where('Date', '>=', date("Y-m-d"))
                ->orderBy('Dates.Date','ASC')
                ->orderBy('Dates.Time','ASC')
                ->get();
        return response()->json($rows);
    }
    public function find(Request $request) {
        $rows = Date::select('Dates.*', 'Peoples.CardCode', 'Peoples.Name', 'Peoples.Lastname', 'Peoples.Lastname2', 'Groups.Name as GroupName','Status.Name as StatusName')
                ->join('Peoples', 'Peoples.PeopleID','=','Dates.PeopleID')
                ->join('Groups', 'Groups.GroupID','=','Peoples.GroupID')
                ->leftJoin('Status', 'Status.StatusID','=','Peoples.StatusID')
                ->where('Date', '>=', $request->from)
                ->where('Date', '<=', $request->to)
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

            $output["anthropometry"]["Weight"] = ( 
                floatval($output["anthropometry"]["Weight"]) == intval($output["anthropometry"]["Weight"]) ? 
                intval($output["anthropometry"]["Weight"]) : 
                round($output["anthropometry"]["Weight"] * 100) / 100
            );

            $output["anthropometry"]["Height"] = ( 
                floatval($output["anthropometry"]["Height"]) == intval($output["anthropometry"]["Height"]) ? 
                intval($output["anthropometry"]["Height"]) : 
                round($output["anthropometry"]["Height"] * 100) / 100
            );

            $output["anthropometry"]["Temperature"] = ( 
                floatval($output["anthropometry"]["Temperature"]) == intval($output["anthropometry"]["Temperature"]) ? 
                intval($output["anthropometry"]["Temperature"]) : 
                round($output["anthropometry"]["Temperature"] * 100) / 100
            );         

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
            $date->AntHabits = $request->AntHabits;
            $date->Obs = $request->Obs;
            $date->typeofdate = (isset($request->typeofdate) ? $request->typeofdate : '');
            $date->UpdatedUserID = JWTAuth::user()->UserID;
            $date->UpdatedAt = date("Y-m-d H:i:s");
            

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

            $recipes = [];
            if ($request->recipes && is_array($request->recipes)) {
                foreach ($request->recipes as $r) {
                    $recipe = new Recipe();
                    if (isset($r["RecipeID"]) && $r["RecipeID"]!=0) {
                        $recipe = Recipe::find($r["RecipeID"]);
                    } else {
                        $recipe->CreatedUserID = JWTAuth::user()->UserID;
                        $recipe->CreatedAt = date("Y-m-d H:i:s");
                    }

                    if ($r["MedicineID"] == "99999999") {
                        $dd = new Medicine();
                        $dd->Name = (isset($request->MedicineNew) ? $request->MedicineNew : '');
                        $dd->LabID=1;
                        $dd->Active=1;
                        $dd->save();
                        $MedicineID = $dd->MedicineID;
                    } else {
                        $MedicineID = $r["MedicineID"];
                    }

                    $recipe->MedicineID = $MedicineID;
                    $recipe->Dose = $r["Dose"];
                    $recipe->Period = $r["Period"];
                    $recipe->Periodicity = $r["Periodicity"];
                    $recipe->DateID = $date->DateID;
                    $recipe->PeopleID = $date->PeopleID;
                    $recipe->save();
                    $cc = json_decode(json_encode($recipe), true);
                    $recipes[] = $cc;
                }
            }

            $interviews = [];
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
                    $interview->VB = (isset($r["VB"]) && $r["VB"] ? 1 : 0);
                    $interview->DateID = $date->DateID;
                    $interview->PeopleID = $date->PeopleID;
                    $interview->save();
                    $cc = json_decode(json_encode($interview), true);
                    $interviews[] = $cc;
                }
            }

            $certificates = [];
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
                    $cc = json_decode(json_encode($certificate), true);
                    $certificates[] = $cc;
                }
            }

            $orders = [];
            if ($request->orders && is_array($request->orders)) {
                foreach ($request->orders as $r) {
                    $order = new Order();
                    if (isset($r["OrderID"]) && $r["OrderID"]!=0) {
                        $order = Order::find($r["OrderID"]);
                    } else {
                        $order->CreatedUserID = JWTAuth::user()->UserID;
                        $order->CreatedAt = date("Y-m-d H:i:s");
                    } 
                    $order->ExamID = $r["ExamID"];
                    $order->Description = $r["Description"];
                    $order->DateID = $date->DateID;
                    $order->PeopleID = $date->PeopleID;
                    $order->save();
                    $cc = json_decode(json_encode($order), true);
                    $cc["ExamTypeID"] = $r["ExamTypeID"];
                    $orders[] = $cc;
                }
            } 

            if ($request->dropOrders && is_array($request->dropOrders)) {
                foreach ($request->dropOrders as $drop) {
                    Order::find($drop)->delete();
                }
            }
            if ($request->dropCertificates && is_array($request->dropCertificates)) {
                foreach ($request->dropCertificates as $drop) {
                    Certificate::find($drop)->delete();
                }
            }
            if ($request->dropInterviews && is_array($request->dropInterviews)) {
                foreach ($request->dropInterviews as $drop) {
                    Order::find($drop)->delete();
                }
            }
            if ($request->dropRecipes && is_array($request->dropRecipes)) {
                foreach ($request->dropRecipes as $drop) {
                    Recipe::find($drop)->delete();
                }
            }

            if (is_array($request->orders) && count($request->orders) > 0) {
                //23.11.2023: Estado no avanzan automatico
                //$people = People::find($date->PeopleID);
                //$people->GroupID = 2;
                //$people->StatusID = 3;
                //$people->save();

                //$date->DestinationGroupID = 2;
                //$date->StatusID = 3;
                //$date->save();
            }

            /* Surgery ? */
           
            if (isset($date->typeofdate) && $date->typeofdate == 'posop') {
                $date->PeopleSurgeryID = (isset($request->PeopleSurgeryID) ? $request->PeopleSurgeryID : 0);
                $date->save();
            }
            else if (isset($date->typeofdate) && $date->typeofdate == 'diagnosis') {

                // Save diagnosis data.
                if ($request->DiagnosisID == "99999999") {
                    $dd = new Diagnosis();
                    $dd->Name = (isset($request->DiagnosisNew) ? $request->DiagnosisNew : '');
                    $dd->Active=1;
                    $dd->Orden=0;
                    $dd->save();
                    $DiagnosisID = $dd->DiagnosisID;
                } else {
                    $DiagnosisID = $request->DiagnosisID;
                }
                $date->DiagnosisID = $DiagnosisID;
                $date->SurgeryID = $request->SurgeryID;
                $date->save();
                throw new \Exception("id is: " . print_r($date,1));
                if ($date->PeopleSurgeryID && intval($date->PeopleSurgeryID) > 0) { // Updated
                    $ps = PeopleSurgery::find($date->PeopleSurgeryID);
                    $ps->UpdatedUserID = JWTAuth::user()->UserID;
                    $ps->UpdatedAt = date("Y-m-d H:i:s");
                    $ps->SurgeryID = $date->SurgeryID;
                    $ps->DateID = $date->DateID;
                    $ps->save();

                } else { // New surgery
                    
                    $ps = new PeopleSurgery();
                    $ps->PeopleID = $date->PeopleID;
                    $ps->DatePost1 = null;
                    $ps->DatePost2 = null;
                    $ps->DatePost3 = null;
                    $ps->DatePost4 = null;
                    $ps->DatePost5 = null;
                    $ps->DatePost6 = null;
                    $ps->DateMsg1 = "";
                    $ps->DateMsg2 = "";
                    $ps->DateMsg3 = "";
                    $ps->DateMsg4 = "";
                    $ps->DateMsg5 = "";
                    $ps->DateMsg6 = "";
                    $ps->CreatedUserID = JWTAuth::user()->UserID;
                    $ps->CreatedAt = date("Y-m-d H:i:s");
                    $ps->UpdatedUserID = JWTAuth::user()->UserID;
                    $ps->UpdatedAt = date("Y-m-d H:i:s");
                    $ps->SurgeryID = $date->SurgeryID;
                    $ps->DateID = $date->DateID;
                    $ps->save();
                    $date->PeopleSurgeryID = $ps->PeopleSurgeryID;
                    $date->save();
                    
                }
                
            }
            else {
                $date->PeopleSurgeryID = 0;
                $date->save();
            }

            return response()->json([
                "success" => true,
                "DateID" => $date->DateID,
                "orders" => $orders,
                "interviews" => $interviews,
                "recipes" => $recipes,
                "certificates" => $certificates,
                "ant" => $ant
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }

    }
    
}
