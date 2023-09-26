<?php

namespace App\Http\Controllers;

use App\Models\CertificateType;
use Illuminate\Http\Request;
class CertificateTypeController extends Controller
{
    public function index() { 
        $rows = CertificateType::select('CertificateTypes.*')
                ->where('CertificateTypes.Active',1)
                ->orderBy('CertificateTypes.Name','ASC')
                ->get();
        return response()->json($rows);
    }

    public function show($id) {
        return response()->json(CertificateType::find($id));
    }

    public function create(Request $request) {
        $row = CertificateType::create($request->all());
        return response()->json($row, 201);
    }

    public function update($id, Request $request) {
        $row = CertificateType::findOrFail($id);
        $row->update($request->all());
        return response()->json($row, 200);
    }
    public function delete($id) {
        CertificateType::findOrFail($id)->update(["Active"=>0]);//->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
}
