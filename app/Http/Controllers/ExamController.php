<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
class ExamController extends Controller
{
    public function index() {
        $rows = Exam::select('Exams.*','ExamTypes.Name as ExamTypeName')
                ->join('ExamTypes', 'ExamTypes.ExamTypeID', '=', 'Exams.ExamTypeID')
                ->where('Exams.Active',1)
                ->orderBy('Exams.Name','ASC')
                ->get();
        return response()->json($rows);
    }

    public function show($id) {
        return response()->json(Exam::find($id));
    }

    public function create(Request $request) {
        $row = Exam::create($request->all());
        return response()->json($row, 201);
    }

    public function update($id, Request $request) {
        $row = Exam::findOrFail($id);
        $row->Name = $request->Name;
        $row->ExamTypeID = $request->ExamTypeID;
        $row->save();
        //$row->update($request->all());
        return response()->json($row, 200);
    }
    public function delete($id) {
        Exam::findOrFail($id)->update(["Active"=>0]);//->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
}
