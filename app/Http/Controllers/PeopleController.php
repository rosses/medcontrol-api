<?php

namespace App\Http\Controllers;

use App\Models\Anthropometry;
use App\Models\Certificate;
use App\Models\Date;
use App\Models\Evolution;
use App\Models\Interview;
use App\Models\People;
use App\Models\Order;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;


class PeopleController extends Controller
{
    public function index() {
        $rows = People::select('Peoples.*','Groups.Name as GroupName')
                ->join('Groups','Groups.GroupID','=','Peoples.GroupID')
                ->orderBy('Peoples.Name','ASC')
                ->get();
        return response()->json($rows);
    }
    public function create(Request $request) {

        try {
            if ($request->CardCode=="") { throw new \Exception("RUT es requerido"); }
            if ($request->Name=="") { throw new \Exception("Nombre es requerido"); }
            if ($request->Lastname=="") { throw new \Exception("Apellido es requerido"); }

            $CC = str_replace(".","",$request->CardCode);
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
                $row = $found[0];
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

    public function show($id) {
        return response()->json(People::find($id));
    }
    public function datesForPeople($id) {
        $rows = Date::select("Dates.*","Users.Name as UpdatedUserName")
                ->where("PeopleID",$id)
                ->join("Users","Users.UserID","=","Dates.UpdatedUserID")
                ->orderBy("Date","Desc")->get();
        
        $output = [];
        foreach ($rows as $row) {
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
            
            $output[] = $row;
        }
        return response()->json($output);
    }
    public function update($id, Request $request) {
        $row = People::findOrFail($id);
        $row->update($request->all());
        return response()->json($row, 200);
    }
}
