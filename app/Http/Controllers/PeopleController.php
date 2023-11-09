<?php

namespace App\Http\Controllers;

use App\Models\Anthropometry;
use App\Models\Certificate;
use App\Models\Date;
use App\Models\Diagnosis;
use App\Models\Evolution;
use App\Models\Interview;
use App\Models\People;
use App\Models\Exam;
use App\Models\ExamData;
use App\Models\Order;
use App\Models\Recipe;
use App\Models\Surgery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;


class PeopleController extends Controller
{
    public function index(Request $request) {
        $rows = People::select('Peoples.*','Groups.Name as GroupName','Healths.Name as HealthName','Status.Name as StatusName')
                ->join('Groups','Groups.GroupID','=','Peoples.GroupID')
                ->leftJoin('Healths','Healths.HealthID','=','Peoples.HealthID')
                ->leftJoin('Status', 'Status.StatusID','=','Peoples.StatusID');
        
                if ($request->Search!="") {
                    $rows = $rows->where(function ($query) use ($request) {
                        $query->where("Peoples.Name","like","%".$request->Search."%")
                            ->orWhere("Peoples.Lastname","like","%".$request->Search."%");
                    });
                }
                if ($request->StatusID!="") {
                    $rows = $rows->where("Peoples.StatusID", $request->StatusID);
                }
                if ($request->HealthID!="") {
                    $rows = $rows->where("Peoples.HealthID", $request->StatusID);
                }        
        
        $rows = $rows->orderBy('Peoples.Name','ASC')->get();
        return response()->json($rows);
    }
    public function create(Request $request) {

        try {
            if ($request->CardCode=="") { throw new \Exception("RUT es requerido"); }
            if ($request->Name=="") { throw new \Exception("Nombre es requerido"); }
            if ($request->Lastname=="") { throw new \Exception("Apellido es requerido"); }

            $CC = str_replace([".",","],["",""],$request->CardCode);
            $CC = substr($CC,0,-1 ).'-'.substr($CC,strlen($CC)-1,1);
            // Modo?
            if ($request->Mode == "fast") {
                if (strlen($request->dates["date"]) < 10 || strlen($request->dates["time"])<5) {
                    throw new \Exception("Fecha y hora son requeridos");
                }
            }
            // Existe?
            $found = People::where("CardCode",$CC)->get();
            if (count($found)==0) {
                $row = new People();
                $row->CardCode = $CC;
                $row->Name = mb_strtoupper(($request->Name),'utf-8');
                $row->Lastname = mb_strtoupper(($request->Lastname),'utf-8');
                $row->Lastname2 = mb_strtoupper(($request->Lastname2 ? $request->Lastname2 : ''),'utf-8');
                $row->Email = ($request->Email ? $request->Email : '');
                $row->Phone = ($request->Phone ? $request->Phone : '');
                $row->Phone2 = ($request->Phone ? $request->Phone2 : '');
                $row->Address = ($request->Address ? $request->Address : '');
                $row->Birthday = ($request->Birthday ? $request->Birthday : null);
                $row->Address = mb_strtoupper(($request->Address ? $request->Address : ''),'utf-8');
                $row->County = mb_strtoupper(($request->County ? $request->County : ''),'utf-8');
                $row->City = mb_strtoupper(($request->City ? $request->City : ''),'utf-8');
                $row->HealthID = ($request->Health ? $request->HealthID : 0);
                $row->Profession = ($request->Profession ? $request->Profession : '');
                $row->Obs = ($request->Obs ? $request->Obs : '');
                $row->GroupID = 1;
                $row->CreatedUserID = JWTAuth::user()->UserID;
                $row->CreatedAt = date("Y-m-d H:i:s");
                $row->UpdatedUserID = JWTAuth::user()->UserID;
                $row->UpdatedAt = date("Y-m-d H:i:s");
                $row->save();
            } else {
                throw new \Exception("RUT ya existe");
                //$row = $found[0];
            }

            if ($request->Mode == "fast") {
                $date = new Date();
                $date->PeopleID = $row->PeopleID;
                $date->Date = $request->dates["date"];
                $date->Time = $request->dates["time"];
                $date->CreatedGroupID = $row->GroupID;
                $date->CreatedUserID = JWTAuth::user()->UserID;
                $date->CreatedAt = date("Y-m-d H:i:s");
                $date->UpdatedUserID = JWTAuth::user()->UserID;
                $date->UpdatedAt = date("Y-m-d H:i:s");
                $date->save();
            } else if ($request->Mode == "full") {
                $date = new Date();
                $date->PeopleID = $row->PeopleID;
                $date->Date = date("Y-m-d");
                $date->Time = date("H:i:s");
                $date->CreatedGroupID = $row->GroupID;
                $date->CreatedUserID = JWTAuth::user()->UserID;
                $date->CreatedAt = date("Y-m-d H:i:s");
                $date->UpdatedUserID = JWTAuth::user()->UserID;
                $date->UpdatedAt = date("Y-m-d H:i:s");
                $date->save();
            }

            return response()->json([
                "success" => true,
                "data" => $row
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }
       
    }
    public function show($id) {
        $rows = People::select('Peoples.*','Groups.Name as GroupName','Healths.Name as HealthName','Status.Name as StatusName')
                ->join('Groups','Groups.GroupID','=','Peoples.GroupID')
                ->leftJoin('Healths','Healths.HealthID','=','Peoples.HealthID')
                ->leftJoin('Status', 'Status.StatusID','=','Peoples.StatusID')
                ->where("PeopleID",$id)
                ->first();

        $people = json_decode(json_encode(($rows)),true);
        $people["Anthropometry"] = Anthropometry::where("PeopleID", $id)->orderBy("AnthropometryID","DESC")->first();
        $people["Date"] = Date::where("PeopleID", $id)->orderBy("DateID","DESC")->first();
        $people["Surgerys"] = Date::select(
                                "Dates.*",
                                "Surgerys.Name as SurgeryName",
                                "Diagnosis.Name as DiagnosisName"
                              )
                                ->join("Surgerys","Surgerys.SurgeryID","=","Dates.SurgeryID")
                                ->leftJoin("Diagnosis","Diagnosis.DiagnosisID","=","Dates.DiagnosisID")
                                ->where("Dates.PeopleID", $id)
                                ->where("Dates.SurgeryID",">",0)
                                ->orderBy("Dates.DateID","DESC")
                                ->get();
        return response()->json($people); 
    }
    public function datesForPeople($id) {
        $rows = Date::select(
                    "Dates.*",
                    "Users.Name as UpdatedUserName",
                    DB::raw("(SELECT COUNT(*) FROM Certificates X WHERE X.DateID = Dates.DateID) as certificates"),
                    DB::raw("(SELECT COUNT(*) FROM Recipes X WHERE X.DateID = Dates.DateID) as recipes"),
                    DB::raw("(SELECT COUNT(*) FROM Interviews X WHERE X.DateID = Dates.DateID) as interviews"),
                    DB::raw("(SELECT COUNT(*) FROM Orders X WHERE X.DateID = Dates.DateID) as orders"),
                )
                ->where("PeopleID",$id)
                ->join("Users","Users.UserID","=","Dates.UpdatedUserID")
                ->orderBy("Date","Desc")->get();
        
        $output = [];
        foreach ($rows as $row) {
            /*
            $row["certificates"] = Certificate::where("DateID", $row->DateID)->get();
            $row["recipes"] = Recipe::where("DateID", $row->DateID)->get();
            $row["interviews"] = Interview::where("DateID", $row->DateID)->get();
            $row["orders"] = Order::select("Orders.*","Exams.ExamTypeID")
                             ->join("Exams","Exams.ExamID","=","Orders.ExamID")
                             ->where("DateID", $row->DateID)->get();
            $row["evolutions"] = Evolution::where("DateID", $row->DateID)->get();
            
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
            $row["anthropometry"] = $ap;
            */
            $output[] = $row;
        }
        return response()->json($output);
    }
    public function examsForPeople($id) {

        $output = [];
        $packs = Order::select(
                        'Orders.DateID',
                        'Dates.Date as Date',
                        'Dates.Time as Time',
                    )
                    ->leftJoin('Dates', 'Dates.DateID', '=', 'Orders.DateID')
                    ->where("Orders.PeopleID",$id)
                    ->groupBy("Orders.DateID","Dates.Date","Dates.Time")
                    ->orderBy("Orders.DateID","DESC")
                    ->get();
        foreach ($packs as $pack) {
            $exams = Order::select(
                'Dates.Date as Date',
                'Dates.Time as Time',
                'Exams.ExamTypeID',
                'Exams.Name as ExamName',
                'ExamTypes.Name as ExamTypeName'
            )
            ->leftJoin('Dates', 'Dates.DateID', '=', 'Orders.DateID')
            ->join('Exams', 'Exams.ExamID', '=', 'Orders.ExamID')
            ->join('ExamTypes', 'ExamTypes.ExamTypeID', '=', 'Exams.ExamTypeID')
            //->where('Exams.Active',1)
            ->where('Orders.PeopleID', $id)
            ->orderBy('ExamTypes.Name','ASC')
            ->groupBy(
                'Dates.Date',
                'Dates.Time',
                'Exams.ExamTypeID',
                'Exams.Name',
                'ExamTypes.Name'
            )
            ->get();

            $rows  = [];
            $acc = [ "ExamTypeName" => "", "ExamTypeID" => "", "Exams" => [] ];
            $lastExamTypeID = "";
            foreach ($exams as $ex) {
                if ($lastExamTypeID!="" && $ex->ExamTypeID != $lastExamTypeID) {
                    $rows[] = $acc;
                    $acc = [ "ExamTypeName" => "", "ExamTypeID" => "", "Exams" => [] ];
                }
                $acc["ExamTypeID"] = $ex->ExamTypeID;
                $acc["ExamTypeName"] = $ex->ExamTypeName;
                $acc["Exams"][] = $ex->ExamName;

                $lastExamTypeID = $ex->ExamTypeID;
            }
            if (count($acc)>0) {
                $rows[] = $acc;
            }

            $output[] = [
                "DateID" => $pack->DateID,
                "Date" => $pack->Date,
                "Time" => $pack->Time,
                "data" => $rows
            ];
        } 


        return response()->json($output);
    }
    public function evolutionsForPeople($id) {
        $evolutions =  Evolution::select("Evolutions.*", "Users.Name as CreatedByName")
                            ->join("Users","Users.UserID","=","Evolutions.CreatedUserID")
                            ->where("Evolutions.PeopleID", $id)
                            ->get();
                            
        return response()->json($evolutions);
    }
    
    public function update($id, Request $request) {
        $row = People::find($id);
        $row->Birthday = $request->Birthday;
        $row->HealthID = $request->HealthID;
        $CC = str_replace([".",","],["",""],$request->CardCode);
        $CC = substr($CC,0,-1 ).'-'.substr($CC,strlen($CC)-1,1);
        $row->CardCode = $CC;
        $row->Name = $request->Name;
        $row->Lastname = $request->Lastname;
        $row->Lastname2 = $request->Lastname2;
        $row->Address = $request->Address;
        $row->Email = $request->Email;
        $row->County = $request->County;
        $row->City = $request->City;
        $row->Obs = $request->Obs;
        $row->Phone = $request->Phone;
        $row->Phone2 = $request->Phone2;
        $row->Profession = $request->Profession;
        $row->Obs = $request->Obs;
        $row->save();
        return response()->json($row, 200);
    }
    public function delete($id) {
        try {
            $row = People::find($id);
            if ($row) {
                $row->delete();
            }
            Date::where("PeopleID",$id)->delete();
            Anthropometry::where("PeopleID",$id)->delete();
            Evolution::where("PeopleID",$id)->delete(); 
            Certificate::where("PeopleID",$id)->delete();
            Interview::where("PeopleID",$id)->delete();
            Order::where("PeopleID",$id)->delete();
            Recipe::where("PeopleID",$id)->delete();

            return response()->json($row, 200);
        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }
    }
}
