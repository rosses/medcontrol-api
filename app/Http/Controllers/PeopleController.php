<?php

namespace App\Http\Controllers;

use App\Models\Anthropometry;
use App\Models\Certificate;
use App\Models\Date;
use App\Models\Status;
use App\Models\Diagnosis;
use App\Models\Evolution;
use App\Models\Interview;
use App\Models\People;
use App\Models\Exam;
use App\Models\ExamData;
use App\Models\Order;
use App\Models\Recipe;
use App\Models\Surgery;
use App\Models\GroupSingle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Tymon\JWTAuth\Facades\JWTAuth;


class PeopleController extends Controller
{
    public function index(Request $request) {

        $offset = ($request->has("page") && $request->get("page")>1 ? ( ($request->get("page") - 1) * 15) : 0);
        $rows = People::select(
            'Peoples.*',
            'Groups.Name as GroupName',
            'Healths.Name as HealthName',
            'Status.Name as StatusName',
            DB::raw("(SELECT TOP 1 CONVERT(varchar(10),X.Date,103) FROM Dates X WHERE X.PeopleID = Peoples.PeopleID ORDER BY [Date] DESC) as LastDate")
        )
        ->join('Groups','Groups.GroupID','=','Peoples.GroupID')
        ->leftJoin('Healths','Healths.HealthID','=','Peoples.HealthID')
        ->leftJoin('Status', 'Status.StatusID','=','Peoples.StatusID');

        if ($request->Search!="") {
            $rows = $rows->where(function ($query) use ($request) {
                $query->where("Peoples.Name","like","%".$request->Search."%")
                    ->orWhere("Peoples.Lastname","like","%".$request->Search."%")
                    ->orWhereRaw("CONCAT(Peoples.Name, ' ', Peoples.Lastname) like '%".$request->Search."%'");
            });
        }
        if ($request->StatusID!="") {
            $rows = $rows->where("Peoples.StatusID", "=", $request->StatusID);
        }
        if ($request->HealthID!="") {
            $rows = $rows->where("Peoples.HealthID", "=", $request->HealthID);
        }
        
        $rows = $rows->orderBy('Peoples.CreatedAt','DESC')->offset($offset)->limit(15);
        $rows = $rows->get(); 


        // Pagination data
        if ($request->Search!="" || $request->StatusID!="" || $request->HealthID!="")  {
            $total = People::select("*");
            if ($request->Search!="") {
                $total = $total->where(function ($query) use ($request) {
                    $query->where("Peoples.Name","like","%".$request->Search."%")
                    ->orWhere("Peoples.Lastname","like","%".$request->Search."%")
                    ->orWhereRaw("CONCAT(Peoples.Name, ' ', Peoples.Lastname) like '%".$request->Search."%'");
                });
            }
            if ($request->StatusID!="") {
                $total = $total->where("Peoples.StatusID", "=", $request->StatusID);
            }
            if ($request->HealthID!="") {
                $total = $total->where("Peoples.HealthID", "=", $request->HealthID);
            }
            $total = $total->count();
        } else {
            $total = People::count();
        }
        // End pagination data

        return response()->json([
            "total" => $total,
            "data" => $rows 
        ]);
    } 
    public function create(Request $request) {

        try {
            if ($request->CardCode=="") { throw new \Exception("RUT es requerido"); }
            if ($request->Name=="" && $request->Mode!="newdate") { throw new \Exception("Nombre es requerido"); }
            if ($request->Lastname=="" && $request->Mode!="newdate") { throw new \Exception("Apellido es requerido"); }

            $CC = str_replace([".",",","-"],["","",""],$request->CardCode);
            $CC = substr($CC,0,-1 ).'-'.substr($CC,strlen($CC)-1,1);
            // Modo?
            if ($request->Mode == "fast" || $request->Mode == "newdate") {
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
                $row->HealthID = ($request->HealthID ? $request->HealthID : 0);
                $row->Profession = ($request->Profession ? $request->Profession : '');
                $row->Obs = ($request->Obs ? $request->Obs : '');
                $row->GroupID = 1;
                $row->StatusID = 1;
                if ($request->Genre) {
                    $row->Genre = $request->Genre;
                }
                $row->BudgetPlace = "CLINICA PV";
                $row->CreatedUserID = JWTAuth::user()->UserID;
                $row->CreatedAt = date("Y-m-d H:i:s");
                $row->UpdatedUserID = JWTAuth::user()->UserID;
                $row->UpdatedAt = date("Y-m-d H:i:s");
                $row->save();
            } else if ($request->Mode!="newdate") {
                throw new \Exception("RUT ya existe");
                //$row = $found[0];
            } else if ($request->Mode=="newdate") {
                $row = $found[0];
            }

            //PeopleDates
            $pd = Date::where("PeopleID", $row->PeopleID)->OrderBy("Dates.DateID","DESC")->get();
            if (count($pd)>0) {
                // Get last date
                $f = $pd[0];                
            }

            $date = new Date();
            if ($request->Mode == "fast") {
                $date = new Date();
                if (isset($f)) {
                    //$date->CreatedGroupID = $f->DestinationGroupID;
                    //$date->StatusID = $f->StatusID;
                    $date->SurgeryID = $f->SurgeryID;
                    $date->DiagnosisID = $f->DiagnosisID;
                } else {
                    //$date->CreatedGroupID = 1;
                    //$date->StatusID = 1;
                }
                $date->PeopleID = $row->PeopleID;
                $date->Date = $request->dates["date"];
                $date->Time = $request->dates["time"];
                //$date->CreatedGroupID = $row->GroupID;
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
                //$date->CreatedGroupID = $row->GroupID;
                $date->CreatedUserID = JWTAuth::user()->UserID;
                $date->CreatedAt = date("Y-m-d H:i:s");
                $date->UpdatedUserID = JWTAuth::user()->UserID;
                $date->UpdatedAt = date("Y-m-d H:i:s");
                $date->save();
                
            } else if ($request->Mode == "newdate") {
                $date = new Date();
                if (isset($f)) {
                    // si es nueva consulta, y existe previa heredar TODO

                    $date->Obs = $f->Obs;
                    $date->DiagnosisID = $f->DiagnosisID;
                    $date->SurgeryID = $f->SurgeryID;
                    $date->SurgeryObs = $f->SurgeryObs;
                    $date->AntDrugs = $f->AntDrugs;
                    $date->AntAllergy = $f->AntAllergy;
                    $date->AntHabits = $f->AntHabits;
                    $date->AntSurgical = $f->AntSurgical;
                    $date->AntMedical = $f->AntMedical;
                    
                } 
                $date->PeopleID = $row->PeopleID;
                $date->Date = $request->dates["date"];
                $date->Time = $request->dates["time"]; 
                $date->CreatedUserID = JWTAuth::user()->UserID;
                $date->CreatedAt = date("Y-m-d H:i:s");
                $date->UpdatedUserID = JWTAuth::user()->UserID;
                $date->UpdatedAt = date("Y-m-d H:i:s");
                $date->save();

                // Antropometria
                $ap = new Anthropometry();
                $ap->DateID = $date->DateID;
                $ap->Weight = 0;
                $ap->Height = 0;
                $ap->Sistolic = 0;
                $ap->Diastolic = 0;
                $ap->Temperature = 0;
                $ap->PeopleID = $row->PeopleID;
                $ap->CreatedUserID = JWTAuth::user()->UserID;
                $ap->CreatedAt = date("Y-m-d H:i:s");
                if (isset($f)) {
                    $antes_ap = Anthropometry::where("DateID",$f->DateID)->first();
                    if ($antes_ap) {
                        $ap->Weight = $antes_ap->Weight;
                        $ap->Height = $antes_ap->Height;
                        $ap->Sistolic = $antes_ap->Sistolic;
                        $ap->Diastolic = $antes_ap->Diastolic;
                        $ap->Temperature = $antes_ap->Temperature;
                    }
                }
                $ap->save();
            }

            return response()->json([
                "success" => true,
                "data" => $row,
                "date" => $date
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }
       
    }
    public function changeDates($id, Request $request) {
        try {


            $p = People::find($id);
            if ($request->DateAsEvaluation) {
                $p->DateAsEvaluation = $request->DateAsEvaluation;
            }
            if ($request->DateAsFinish) {
                $p->DateAsFinish = $request->DateAsFinish;
            }
            if ($request->DateAsSurgery) {
                $p->DateAsSurgery = $request->DateAsSurgery;
            }
            $p->save();

            return response()->json([
                "success" => true,
                "data" => $p
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }
    }
    public function changeStatus($id, Request $request) {
        try {

            $s=Status::find($request->StatusID);

            $p = People::find($id);
            $p->StatusID = $request->StatusID;
            $p->GroupID = $s->GroupID;
            $p->save();

            return response()->json([
                "success" => true
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }
    }
    public function show($id) {
        $rows = People::select('Peoples.*','Groups.Name as GroupName','Healths.Name as HealthName','Status.Name as StatusName', 'BudgetStatus.Name as BudgetStatusName')
                ->join('Groups','Groups.GroupID','=','Peoples.GroupID')
                ->leftJoin('Healths','Healths.HealthID','=','Peoples.HealthID')
                ->leftJoin('BudgetStatus','BudgetStatus.BudgetStatusID','=','Peoples.BudgetStatusID')
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

        $people["RequestedOrders"] = Order::select(
                                        "Exams.Name as ExamName",
                                        "ExamTypes.Name as ExamTypeName",
                                        "ExamDatas.Name as DataName",
                                        DB::raw("(CASE WHEN ExamDataValues.Value = '1' THEN 'checked' ELSE '' END) as Value")
                                     )
                            ->join("Exams","Exams.ExamID","=","Orders.ExamID")
                            ->join("ExamTypes","ExamTypes.ExamTypeID","=","Exams.ExamTypeID")
                            ->join("ExamDatas","ExamDatas.ExamID","=","Exams.ExamID")
                            ->leftJoin("ExamDataValues", function($join) {
                                $join->on("ExamDataValues.ExamDataID","=","ExamDatas.ExamDataID");
                                $join->on("ExamDataValues.OrderID","=","Orders.OrderID");
                            })
                            ->where("Orders.PeopleID",$id)
                            ->where("ExamDatas.ExamDataType","=","boolean")
                            ->get();

        $people["RequestedInterviews"] = Interview::select(
                                        "Interviews.*",
                                        "Specialists.Name as SpecialistName"
                                     )
                            ->leftJoin("Specialists","Specialists.SpecialistID","=","Interviews.SpecialistID")
                            ->where("Interviews.PeopleID",$id)
                            ->where("Interviews.VB","1")
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
                ->leftJoin("Users","Users.UserID","=","Dates.UpdatedUserID")
                ->orderBy("Date","Desc")->get();
        
        return response()->json($rows);
    }
    public function examsForPeople($id) {

        $output = [];
        // BASED ON SINGLE
        $packs = GroupSingle::select(
                        'GroupSingles.GroupSingleID',
                        DB::raw('CONVERT(varchar(10), GroupSingles.CreatedAt, 120) as Date'),
                        DB::raw('CONVERT(varchar(5), GroupSingles.CreatedAt, 108) as Time'),
                        DB::raw('\'GS\' as OrderType'),
                    )
                    ->join("Orders","Orders.GroupSingleID","=","GroupSingles.GroupSingleID")
                    ->where("Orders.PeopleID",$id)
                    ->where("GroupSingles.Type","=","order")
                    ->orderBy("GroupSingles.CreatedAt","DESC")
                    ->get();
        foreach ($packs as $pack) {
            $exams = Order::select(
                DB::raw('CONVERT(varchar(10), GroupSingles.CreatedAt, 120) as Date'),
                DB::raw('CONVERT(varchar(5), GroupSingles.CreatedAt, 108) as Time'),
                'Exams.ExamTypeID',
                'Exams.Name as ExamName',
                'ExamTypes.Name as ExamTypeName'
            )
            ->leftJoin('GroupSingles', 'GroupSingles.GroupSingleID', '=', 'Orders.GroupSingleID')
            ->join('Exams', 'Exams.ExamID', '=', 'Orders.ExamID')
            ->join('ExamTypes', 'ExamTypes.ExamTypeID', '=', 'Exams.ExamTypeID')
            ->where('Orders.PeopleID', $id)
            ->where('Orders.GroupSingleID', $pack->GroupSingleID)
            ->orderBy('ExamTypes.Name','ASC')
            ->groupBy(
                DB::raw('CONVERT(varchar(10), GroupSingles.CreatedAt, 120)'),
                DB::raw('CONVERT(varchar(5), GroupSingles.CreatedAt, 108)'),
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
            if (count($acc)>0) { $rows[] = $acc; }
            $output[] = [
                "DateID" => $pack->GroupSingleID,
                "Date" => $pack->Date,
                "Time" => $pack->Time,
                "OrderType" => $pack->OrderType,
                "data" => $rows,
            ];
        } 

        /// BASED ON DATES 
        $packs = Order::select(
                        'Orders.DateID as DateID',
                        'Dates.Date as Date',
                        'Dates.Time as Time',
                        DB::raw('\'DT\' as OrderType'),
                    )
                    ->leftJoin('Dates', 'Dates.DateID', '=', 'Orders.DateID')
                    ->where("Orders.PeopleID",$id)
                    ->where("Orders.DateID",">","0")
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
            ->where('Orders.PeopleID', $id)
            ->where('Orders.DateID', $pack->DateID)
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
                "OrderType" => $pack->OrderType,
                "data" => $rows
            ];
        } 


        return response()->json($output);
    }
    public function evolutionsForPeople($id) {
        $evolutions =  Evolution::select("Evolutions.*", "Users.Name as CreatedByName")
                            ->join("Users","Users.UserID","=","Evolutions.CreatedUserID")
                            ->where("Evolutions.PeopleID", $id)
                            ->orderBy("CreatedAt","DESC")
                            ->get();
                            
        return response()->json($evolutions);
    }
    public function interviewsForPeople($id) {
        $interviews =  Interview::select("Interviews.*", "Users.Name as CreatedByName","Specialists.Name as SpecialistName")
                            ->join("Users","Users.UserID","=","Interviews.CreatedUserID")
                            ->join("Specialists","Specialists.SpecialistID","=","Interviews.SpecialistID")
                            ->where("Interviews.PeopleID", $id)
                            ->orderBy("CreatedAt","DESC")
                            ->get();
                            
        return response()->json($interviews);
    }
    public function recipesForPeople($id) {

        $output = [];

        // BASED ON SINGLE
        $packs = GroupSingle::select(
            'GroupSingles.GroupSingleID',
            DB::raw('CONVERT(varchar(10), GroupSingles.CreatedAt, 120) as Date'),
            DB::raw('CONVERT(varchar(5), GroupSingles.CreatedAt, 108) as Time'),
            DB::raw('\'GS\' as OrderType'),
        )
        ->join("Recipes","Recipes.GroupSingleID","=","GroupSingles.GroupSingleID")
        ->where("Recipes.PeopleID",$id)
        ->where("GroupSingles.Type","=","recipe")
        ->orderBy("GroupSingles.CreatedAt","DESC")
        ->get();


        foreach ($packs as $pack) {

            $meds = Recipe::select(
                DB::raw('CONVERT(varchar(10), GroupSingles.CreatedAt, 120) as Date'),
                DB::raw('CONVERT(varchar(5), GroupSingles.CreatedAt, 108) as Time'),
                'Medicines.Name as MedicineName',
                'Recipes.Dose',
                'Recipes.Period',
                'Recipes.Periodicity',
            )
            ->leftJoin('GroupSingles', 'GroupSingles.GroupSingleID', '=', 'Recipes.GroupSingleID')
            ->join('Medicines', 'Medicines.MedicineID', '=', 'Recipes.MedicineID') 
            ->where('Recipes.PeopleID', $id)
            ->where('GroupSingles.GroupSingleID', $pack->GroupSingleID)
            ->groupBy(
                DB::raw('CONVERT(varchar(10), GroupSingles.CreatedAt, 120)'),
                DB::raw('CONVERT(varchar(5), GroupSingles.CreatedAt, 108)'),
                'Medicines.Name',
                'Recipes.Dose',
                'Recipes.Period',
                'Recipes.Periodicity'
            )
            ->get();

            $rows  = [];
            foreach ($meds as $m) {
                $rows[] = $m;
            }

            $output[] = [
                "DateID" => $pack->GroupSingleID,
                "Date" => $pack->Date,
                "Time" => $pack->Time,
                "OrderType" => $pack->OrderType,
                "data" => $rows
            ];
        } 

        // BY DATES
        $packs = Recipe::select(
                        'Recipes.DateID',
                        'Dates.Date as Date',
                        'Dates.Time as Time',
                        DB::raw('\'DT\' as OrderType'),
                    )
                    ->leftJoin('Dates', 'Dates.DateID', '=', 'Recipes.DateID')
                    ->join('Medicines', 'Medicines.MedicineID', '=', 'Recipes.MedicineID') 
                    ->where('Recipes.PeopleID', $id)
                    ->where('Recipes.DateID',">","0")
                    ->groupBy("Recipes.DateID","Dates.Date","Dates.Time")
                    ->orderBy("Recipes.DateID","DESC")
                    ->get();

        foreach ($packs as $pack) {

            $meds = Recipe::select(
                'Dates.Date as Date',
                'Dates.Time as Time',
                'Medicines.Name as MedicineName',
                'Recipes.Dose',
                'Recipes.Period',
                'Recipes.Periodicity',
            )
            ->leftJoin('Dates', 'Dates.DateID', '=', 'Recipes.DateID')
            ->join('Medicines', 'Medicines.MedicineID', '=', 'Recipes.MedicineID') 
            ->where('Recipes.PeopleID', $id)
            ->where('Recipes.DateID', $pack->DateID)
            ->groupBy(
                'Dates.Date',
                'Dates.Time',
                'Medicines.Name',
                'Recipes.Dose',
                'Recipes.Period',
                'Recipes.Periodicity'
            )
            ->get();

            $rows  = [];
            foreach ($meds as $m) {
                $rows[] = $m;
            }

            $output[] = [
                "DateID" => $pack->DateID,
                "Date" => $pack->Date,
                "Time" => $pack->Time,
                "OrderType" => $pack->OrderType,
                "data" => $rows
            ];
        } 

        return response()->json($output);
    }
    public function certificatesForPeople($id) {

        $packs = Certificate::select(
            'Certificates.*',
            'CertificateTypes.Name as CertificateTypeName',
            'Dates.Date as Date',
            'Dates.Time as Time',
        )
        ->leftJoin('CertificateTypes', 'CertificateTypes.CertificateTypeID', '=', 'Certificates.CertificateTypeID')
        ->leftJoin('Dates', 'Dates.DateID', '=', 'Certificates.DateID')
        ->where("Certificates.PeopleID",$id)
        ->orderBy("Certificates.DateID","DESC")
        ->get();

        return response()->json($packs);
    }
    public function update($id, Request $request) {
        $row = People::find($id);
        $row->Birthday = $request->Birthday;
        $row->HealthID = $request->HealthID;
        $CC = str_replace([".",",","-"],["","",""],$request->CardCode);
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
        if (isset($request->Genre)) {
            $row->Genre = $request->Genre;
        }
        $row->BudgetPlace = $request->BudgetPlace;
        $row->BudgetStatusID = $request->BudgetStatusID;
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
    public function getText($id) {

        try {
            $txt = date("Y-m-d H:i:s")."\n";        
            $people = People::select('Peoples.*','Groups.Name as GroupName','Healths.Name as HealthName','Status.Name as StatusName')
            ->join('Groups','Groups.GroupID','=','Peoples.GroupID')
            ->leftJoin('Healths','Healths.HealthID','=','Peoples.HealthID')
            ->leftJoin('Status', 'Status.StatusID','=','Peoples.StatusID')
            ->where("PeopleID", $id);    
            
            $people = $people->first();

            $ant = Anthropometry::where("PeopleID", $id)->orderBy("AnthropometryID","DESC")->first();

            $evolutions =  Evolution::select("Evolutions.*", "Users.Name as CreatedByName")
                                ->join("Users","Users.UserID","=","Evolutions.CreatedUserID")
                                ->where("Evolutions.PeopleID", $id)
                                ->get();

           
            if ($people->Birthday=="") {
                $people->Birthday = date("Y-m-d H:i:s");
            }
            $fecha_nac = new \DateTime(date('Y/m/d',strtotime($people->Birthday))); 
            $fecha_hoy =  new \DateTime(date('Y/m/d',time())); 
            $edad = date_diff($fecha_hoy,$fecha_nac); 
            $imc = 0;

            $edv = DB::select("
            SELECT		E.ExamID,  ET.Name ExamTypeName, E.Name, ED.ExamDataType, ED.Name ExamDataName, EDV.Value 
            FROM		Exams as E 
            INNER JOIN	ExamTypes ET ON ET.ExamTypeID = E.ExamTypeID
            INNER JOIN	ExamDatas ED ON ED.ExamID = E.ExamID 
            INNER JOIN	ExamDataValues EDV ON EDV.ExamDataID = ED.ExamDataID 
            INNER JOIN  Orders O ON O.PeopleID = '".$id."' AND O.OrderID = EDV.OrderID 
            WHERE		E.Active = 1 
            ORDER BY	ET.Name ASC, EDV.ExamDataValueID DESC
            ");
            $edv = json_decode(json_encode($edv), true);
            $results = [];
            foreach ($edv as $rr) {
                if (!isset($results[$rr["ExamTypeName"]][$rr["ExamDataName"]])) { // Only newest result
                    if ($rr["ExamDataType"]=="boolean") {
                        if ($rr["Value"]=="1") {
                            $results[$rr["ExamTypeName"]][$rr["ExamDataName"]] = "OK";
                        }
                        else {
                            $results[$rr["ExamTypeName"]][$rr["ExamDataName"]] = "NO-OK";
                        }
                    }
                    else if ($rr["ExamDataType"]=="text") { 
                        $results[$rr["ExamTypeName"]][$rr["ExamDataName"]] = $rr["Value"];
                    } 
                    else if ($rr["ExamDataType"]=="number") { 
                        $results[$rr["ExamTypeName"]][$rr["ExamDataName"]] = round($rr["Value"],2);
                    }
                }
            }
            
            
            $surgerys = Date::select(
                "Dates.*",
                "Surgerys.Name as SurgeryName",
                "Diagnosis.Name as DiagnosisName"
            )
                ->join("Surgerys","Surgerys.SurgeryID","=","Dates.SurgeryID")
                ->leftJoin("Diagnosis","Diagnosis.DiagnosisID","=","Dates.DiagnosisID")
                ->where("Dates.PeopleID", $id)
                ->where("Dates.SurgeryID",">",0)
                ->orderBy("Dates.DateID","DESC")
                ->limit(1)
                ->get();
            
            if (count($surgerys)>0) {
                $surgery = $surgerys[0];
            }
        

            $txt .= "\nANTROPOMETRIA\n"; 
            $txt .= "Peso: ".number_format($ant->Weight,0,",",".")." Talla: ".number_format($ant->Height,0,",",".")."  IMC: ".$imc.""; //Temp. ".number_format($ant->Temperature,1,",",".")."
            $txt .= "\n\nANTECEDENTES\n";
            $txt .= "Ciudad donde Vive: ".$people->City."\n";
            $txt .= "Profesión: ".$people->Profession."\n";
            $txt .= "Médicos: ".(isset($surgery) ? $surgery->AntMedical : '')."\n";
            $txt .= "Farmacos: ".(isset($surgery) ? $surgery->AntDrugs : '')."\n";
            $txt .= "Quirúrgicos: ".(isset($surgery) ? $surgery->AntSurgical : '')."\n";
            $txt .= "Alergias: ".(isset($surgery) ? $surgery->AntAllergy : '')."\n"; 
            $txt .= "Hábitos: ".(isset($surgery) ? $surgery->AntHabits : '')."\n"; 

            foreach ($results as $type=>$d) {
                $txt .= "\n".mb_strtoupper($type,'utf-8')."\n";
                foreach ($d as $field=>$val) {
                    $txt .= "".$field.": ".$val."\n";
                }
            } 

            return response()->json([
                "text" => $txt
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }  
    }
}
