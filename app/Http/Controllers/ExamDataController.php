<?php

namespace App\Http\Controllers;

use App\Models\ExamData;
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
    public function getExamValues($DateID, $ExamTypeID) {
        return response()->json([], 200);
    }
    public function create(Request $request) {
        $row = ExamData::create($request->all());
        return response()->json($row, 201);
    }

    public function update($id, Request $request) {
        $row = ExamData::findOrFail($id);
        $row->Name = $request->Name;
        $row->ExamID = $request->ExamID;
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

