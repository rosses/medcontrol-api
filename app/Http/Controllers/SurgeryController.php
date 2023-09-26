<?php

namespace App\Http\Controllers;

use App\Models\Surgery;
use Illuminate\Http\Request;
class SurgeryController extends Controller
{
    public function index() { 
        $rows = Surgery::select('Surgerys.*')
                ->where('Surgerys.Active',1)
                ->orderBy('Surgerys.Name','ASC')
                ->get();
        return response()->json($rows);
    }

    public function show($id) {
        return response()->json(Surgery::find($id));
    }

    public function create(Request $request) {
        $row = Surgery::create($request->all());
        return response()->json($row, 201);
    }

    public function update($id, Request $request) {
        $row = Surgery::findOrFail($id);
        $row->update($request->all());
        return response()->json($row, 200);
    }
    public function delete($id) {
        Surgery::findOrFail($id)->update(["Active"=>0]);//->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
}
