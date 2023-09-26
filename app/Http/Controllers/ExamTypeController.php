<?php

namespace App\Http\Controllers;

use App\Models\ExamType;
use Illuminate\Http\Request;
class ExamTypeController extends Controller
{
    public function index() {
        $rows = ExamType::select('ExamTypes.*')
                ->where('ExamTypes.Active',1)
                ->orderBy('ExamTypes.Name','ASC')
                ->get();
        return response()->json($rows);
    }

    public function show($id) {
        return response()->json(ExamType::find($id));
    }

    public function create(Request $request) {
        $row = ExamType::create($request->all());
        return response()->json($row, 201);
    }

    public function update($id, Request $request) {
        $row = ExamType::findOrFail($id);
        $row->update($request->all());
        return response()->json($row, 200);
    }
    public function delete($id) {
        ExamType::findOrFail($id)->update(["Active"=>0]);//->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
}
