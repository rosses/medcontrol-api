<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
class GroupController extends Controller
{
    public function index() {
        $rows = Group::select('Groups.*')
                ->where('Groups.Active',1)
                ->orderBy('Groups.Name','ASC')
                ->get();
        return response()->json($rows);
    }

    public function show($id) {
        return response()->json(Group::find($id));
    }

    public function create(Request $request) {
        $row = Group::create($request->all());
        return response()->json($row, 201);
    }

    public function update($id, Request $request) {
        $row = Group::findOrFail($id);
        $row->update($request->all());
        return response()->json($row, 200);
    }
    public function delete($id) {
        Group::findOrFail($id)->update(["Active"=>0]);//->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
}
