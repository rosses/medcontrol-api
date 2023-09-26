<?php

namespace App\Http\Controllers;

use App\Models\Specialist;
use Illuminate\Http\Request;
class SpecialistController extends Controller
{
    public function index() { 
        $rows = Specialist::select('Specialists.*')
                ->where('Specialists.Active',1)
                ->orderBy('Specialists.Name','ASC')
                ->get();
        return response()->json($rows);
    }

    public function show($id) {
        return response()->json(Specialist::find($id));
    }

    public function create(Request $request) {
        $row = Specialist::create($request->all());
        return response()->json($row, 201);
    }

    public function update($id, Request $request) {
        $row = Specialist::findOrFail($id);
        $row->update($request->all());
        return response()->json($row, 200);
    }
    public function delete($id) {
        Specialist::findOrFail($id)->update(["Active"=>0]);//->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
}
