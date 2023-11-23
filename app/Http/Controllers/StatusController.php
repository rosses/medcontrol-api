<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;
class StatusController extends Controller
{
    public function index(Request $request) {
        $rows = Status::select('Status.*', 'Groups.Name as GroupName')
                ->leftJoin('Groups','Groups.GroupID','=','Status.GroupID')
                ->where('Status.Active',1)
                ->orderBy('Status.GroupID','ASC')->orderBy('Status.Name','ASC');
        if ($request->GroupID!='') {
            $rows = $rows->where("Status.GroupID",$request->GroupID);
        }
        $rows = $rows->get();
        return response()->json($rows);
    }

   
}
