<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;
class StatusController extends Controller
{
    public function index() {
        $rows = Status::select('Status.*', 'Groups.Name as GroupName')
                ->leftJoin('Groups','Groups.GroupID','=','Status.GroupID')
                ->where('Status.Active',1)
                ->orderBy('Status.Name','ASC')
                ->get();
        return response()->json($rows);
    }

   
}
