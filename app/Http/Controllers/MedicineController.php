<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
class MedicineController extends Controller
{
    public function index() {
        $rows = Medicine::select('Medicines.*','Labs.Name as LabName')
                ->join('Labs', 'Medicines.LabID', '=', 'Labs.LabID')
                ->where('Medicines.Active',1)
                ->orderBy('Medicines.Name','ASC')
                ->get();
        return response()->json($rows);
    }

    public function show($id) {
        return response()->json(Medicine::find($id));
    }

    public function create(Request $request) {
        $row = Medicine::create($request->all());
        return response()->json($row, 201);
    }

    public function update($id, Request $request) {
        $row = Medicine::findOrFail($id);
        $row->update($request->all());
        return response()->json($row, 200);
    }
    public function delete($id) {
        Medicine::findOrFail($id)->update(["Active"=>0]);//->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
}
