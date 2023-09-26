<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use Illuminate\Http\Request;
class DiagnosisController extends Controller
{
    public function index() {
        $rows = Diagnosis::select('Diagnosis.*')
                ->where('Diagnosis.Active',1)
                ->orderBy('Diagnosis.Name','ASC')
                ->get();
        return response()->json($rows);
    }

    public function show($id) {
        return response()->json(Diagnosis::find($id));
    }

    public function create(Request $request) {
        $row = Diagnosis::create($request->all());
        return response()->json($row, 201);
    }

    public function update($id, Request $request) {
        $row = Diagnosis::findOrFail($id);
        $row->update($request->all());
        return response()->json($row, 200);
    }
    public function delete($id) {
        Diagnosis::findOrFail($id)->update(["Active"=>0]);//->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
}
