<?php

namespace App\Http\Controllers;

use App\Models\ExamData;
use Illuminate\Http\Request;
class ExamDataController extends Controller
{
    public function index() {
        $rows = ExamData::select('ExamDatas.*','Exams.Name as ExamName')
                ->join('Exams', 'ExamDatas.ExamID', '=', 'Exams.ExamID')
                ->where('ExamDatas.Active',1)
                ->orderBy('ExamDatas.Name','ASC')
                ->get();
        return response()->json($rows);
    }

    public function show($id) {
        return response()->json(ExamData::find($id));
    }

    public function create(Request $request) {
        $row = ExamData::create($request->all());
        return response()->json($row, 201);
    }

    public function update($id, Request $request) {
        $row = ExamData::findOrFail($id);
        $row->update($request->all());
        return response()->json($row, 200);
    }
    public function delete($id) {
        ExamData::findOrFail($id)->update(["Active"=>0]);//->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
}

