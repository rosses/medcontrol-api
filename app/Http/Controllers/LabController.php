<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use Illuminate\Http\Request;
class LabController extends Controller
{
    public function index() { 
        $rows = Lab::select('Labs.*')
                ->where('Labs.Active',1)
                ->orderBy('Labs.Name','ASC')
                ->get();
        return response()->json($rows);
    }

    public function show($id) {
        return response()->json(Lab::find($id));
    }

    public function create(Request $request) {
        $row = Lab::create($request->all());
        return response()->json($row, 201);
    }

    public function update($id, Request $request) {
        $row = Lab::findOrFail($id);
        $row->update($request->all());
        return response()->json($row, 200);
    }
    public function delete($id) {
        Lab::findOrFail($id)->update(["Active"=>0]);//->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
}
