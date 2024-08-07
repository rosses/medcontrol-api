<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class NotifyController extends Controller
{
    public function total() { 
        $rows = DB::select("SELECT COUNT(*) as X FROM Notifications WHERE ReadAt IS NULL");
        return response()->json(["total"=>$rows[0]->X]);
    }
    public function list() { 
        $rows = DB::select("SELECT TOP 100 * FROM Notifications N LEFT JOIN Peoples P ON P.PeopleID = N.PeopleID");
        return response()->json($rows);
    }
 
    public function read($id) {
        $n = Notification::find($id);
        $n->ReadAt = date("Y-m-d H:i:s");
        $n->save();
        
        return response()->json([
            "success" => true
        ], 200);
    }

    public function create() {
        
        $surgerys = DB::select("SELECT * FROM PeopleSurgerys PS WHERE PS.DateAsSurgery <= DATEADD(day,5,GETDATE()) AND PS.DateAsSurgery >= GETDATE() AND NOT EXISTS ( SELECT * FROM Notifications N WHERE N.PeopleSurgeryID = PS.PeopleSurgeryID AND N.Origin = 'surgery')");
        $finish = DB::select("SELECT * FROM PeopleSurgerys PS WHERE PS.DateAsFinish <= DATEADD(day,5,GETDATE()) AND PS.DateAsFinish >= GETDATE() AND NOT EXISTS ( SELECT * FROM Notifications N WHERE N.PeopleSurgeryID = PS.PeopleSurgeryID AND N.Origin = 'finish')");
        $enter = DB::select("SELECT * FROM PeopleSurgerys PS WHERE PS.DateAsEnter <= DATEADD(day,5,GETDATE()) AND PS.DateAsEnter >= GETDATE() AND NOT EXISTS ( SELECT * FROM Notifications N WHERE N.PeopleSurgeryID = PS.PeopleSurgeryID AND N.Origin = 'enter')");

        foreach ($surgerys as $s) {
            $n = new Notification();
            $n->PeopleID = $s->PeopleID;
            $n->Origin = "surgery";
            $n->PeopleSurgeryID = $s->PeopleSurgeryID;
            $n->Description = "Próxima cirugia programada";
            $n->CreatedAt = date("Y-m-d H:i:s");
            $n->save();
        }
        foreach ($finish as $s) {
            $n = new Notification();
            $n->PeopleID = $s->PeopleID;
            $n->Origin = "finish";
            $n->PeopleSurgeryID = $s->PeopleSurgeryID;
            $n->Description = "Próximo vencimiento examenes";
            $n->CreatedAt = date("Y-m-d H:i:s");
            $n->save();
        }

        foreach ($enter as $s) {
            $n = new Notification();
            $n->PeopleID = $s->PeopleID;
            $n->Origin = "enter";
            $n->PeopleSurgeryID = $s->PeopleSurgeryID;
            $n->Description = "Próximo PAD";
            $n->CreatedAt = date("Y-m-d H:i:s");
            $n->save();
        }
        return response()->json([
            "success" => true
        ], 200);
    }
}
