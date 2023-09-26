<?php

namespace App\Http\Controllers;

use App\Models\Health;
use Illuminate\Http\Request;
class HealthController extends Controller
{
    public function index() { 
        $rows = Health::select('Healths.*')
                ->where('Healths.Active',1)
                ->orderBy('Healths.Name','ASC')
                ->get();
        return response()->json($rows);
    }

    public function show($id) {
        return response()->json(Health::find($id));
    }

    public function create(Request $request) {
        $row = Health::create($request->all());
        return response()->json($row, 201);
    }

    public function update($id, Request $request) {
        $row = Health::findOrFail($id);
        $row->update($request->all());
        return response()->json($row, 200);
    }
    public function delete($id) {
        Health::findOrFail($id)->update(["Active"=>0]);//->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
}
