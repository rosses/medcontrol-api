<?php

namespace App\Http\Controllers;

use App\Models\ExamData;
use App\Models\Order;
use App\Models\ExamDataValue;
use App\Models\GroupSingle;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExamDataController extends Controller
{
    public function index() {
        $rows = ExamData::select('ExamDatas.*','Exams.Name as ExamName','Exams.ExamTypeID','ExamTypes.Name as ExamTypeName',
                                'ExamTypes.Side','ExamTypes.SideOrder','Exams.Orden as ExamOrden')
                ->join('Exams', 'ExamDatas.ExamID', '=', 'Exams.ExamID')
                ->join('ExamTypes', 'ExamTypes.ExamTypeID', '=', 'Exams.ExamTypeID')
                ->where('ExamDatas.Active',1)
                ->orderBy('ExamTypes.Name','ASC')
                ->orderBy('Exams.Name','ASC')
                ->orderBy('ExamDatas.Name','ASC')
                ->get();
        return response()->json($rows);
    }
    public function show($id) {
        return response()->json(ExamData::find($id));
    }
    public function saveResults(Request $request) {
        try {
            
            if (isset($request->DateID) && $request->DateID!="" && intval($request->DateID) > 0) {
                if ($request->data && is_array($request->data)) {
                    foreach ($request->data as $d) {
                        $edv = new ExamDataValue();
                        if (isset($d["ExamDataValueID"]) && $d["ExamDataValueID"]!=0) {
                            $edv = ExamDataValue::find($d["ExamDataValueID"]);
                        }
                        $OrderID = 0;
                        $order = Order::where("DateID", $request->DateID)->where("ExamID", $d["ExamID"])->first();
                        if ($order) {
                            $OrderID = $order->OrderID;
                        }
                        if ($OrderID > 0) {
                            if (isset($d["Value"])) {
                                $edv->OrderID = $OrderID;
                                $edv->DateID = $request->DateID;
                                $edv->GroupSingleID = 0;
                                $edv->ExamDataID = $d["ExamDataID"];
                                $edv->Value = $d["Value"];                        
                                $edv->save();
                            }
                        }
                    }
                }
            }
            else if (isset($request->SingleID) && $request->SingleID!="" && intval($request->SingleID) > 0) {
                $single = GroupSingle::find($request->SingleID);
                if ($request->data && is_array($request->data)) {
                    foreach ($request->data as $d) {
                        $edv = new ExamDataValue();
                        if (isset($d["ExamDataValueID"]) && $d["E84xamDataValueID"]!=0) {
                            $edv = ExamDataValue::find($d["ExamDataValueID"]);
                        }
                        $OrderID = 0;
                        $order = Order::where("GroupSingleID", $request->SingleID)->where("ExamID", $d["ExamID"])->first();
                        if ($order) {
                            $OrderID = $order->OrderID;
                        }
                        if ($OrderID > 0) {
                            if (isset($d["Value"])) {
                                $edv->OrderID = $OrderID;
                                $edv->DateID = 0;
                                $edv->GroupSingleID = $request->SingleID;
                                $edv->ExamDataID = $d["ExamDataID"];
                                $edv->Value = $d["Value"];                        
                                $edv->save();
                            }
                        }
                        else if ($OrderID == 0) {
                          if (isset($d["Value"])) {
                            $od = new Order();
                            $od->ExamID = $d["ExamID"];
                            $od->PeopleID = $single->PeopleID;
                            $od->DateID = 0;
                            $od->CreatedUserID = JWTAuth::user()->UserID;
                            $od->CreatedAt = date("Y-m-d H:i:s");
                            $od->GroupSingleID = $request->SingleID;
                            $od->Comments = "";
                            $od->save();

                            $edv->OrderID = $od->OrderID;
                            $edv->DateID = 0;
                            $edv->GroupSingleID = $request->SingleID;
                            $edv->ExamDataID = $d["ExamDataID"];
                            $edv->Value = $d["Value"];                        
                            $edv->save();
                          }
                        }
                    }
                }
            }
            
            return response()->json([
                "success" => true
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "error" => $e->getMessage()
            ], 400);
        }

    }
    public function getExamValuesByDate($DateID) {
        $rows = ExamDataValue::where("DateID", $DateID)->get();
        return response()->json($rows, 200);
    }
    public function getExamValuesByGroup($GroupSingleID) {
        $rows = ExamDataValue::where("GroupSingleID", $GroupSingleID)->get();
        return response()->json($rows, 200);
    }
    
    public function create(Request $request) {
        $row = ExamData::create($request->all());
        return response()->json($row, 201);
    }

    public function update($id, Request $request) {
        $row = ExamData::findOrFail($id);
        $row->Name = $request->Name;
        $row->ExamID = $request->ExamID;
        $row->ExamDataType = $request->ExamDataType;
        $row->save();
        return response()->json($row, 200);
    }
    public function delete($id) {
        ExamData::findOrFail($id)->update(["Active"=>0]);//->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
}

