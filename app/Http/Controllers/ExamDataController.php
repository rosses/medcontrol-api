<?php

namespace App\Http\Controllers;

use App\Models\ExamData;
use App\Models\Order;
use App\Models\ExamDataValue;
use Exception;
use Illuminate\Http\Request;
class ExamDataController extends Controller
{
    public function index() {
        $rows = ExamData::select('ExamDatas.*','Exams.Name as ExamName','Exams.ExamTypeID')
                ->join('Exams', 'ExamDatas.ExamID', '=', 'Exams.ExamID')
                ->join('ExamTypes', 'ExamTypes.ExamTypeID', '=', 'Exams.ExamTypeID')
                ->where('ExamDatas.Active',1)
                ->orderBy('ExamDatas.Name','ASC')
                ->get();
        return response()->json($rows);
    }
    public function show($id) {
        return response()->json(ExamData::find($id));
    }
    public function saveResults(Request $request) {
        try {
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
                        $edv->OrderID = $OrderID;
                        $edv->DateID = $request->DateID;
                        $edv->ExamDataID = $d["ExamDataID"];
                        $edv->Value = $d["Value"];                        
                        $edv->save();
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

