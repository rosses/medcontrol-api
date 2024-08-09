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
        $datepost1 = DB::select("SELECT * FROM PeopleSurgerys PS WHERE PS.DatePost1 <= DATEADD(day,5,GETDATE()) AND PS.DatePost1 >= GETDATE() AND NOT EXISTS ( SELECT * FROM Notifications N WHERE N.PeopleSurgeryID = PS.PeopleSurgeryID AND N.Origin = 'datepost1')");
        $datepost2 = DB::select("SELECT * FROM PeopleSurgerys PS WHERE PS.DatePost2 <= DATEADD(day,5,GETDATE()) AND PS.DatePost2 >= GETDATE() AND NOT EXISTS ( SELECT * FROM Notifications N WHERE N.PeopleSurgeryID = PS.PeopleSurgeryID AND N.Origin = 'datepost2')");
        $datepost3 = DB::select("SELECT * FROM PeopleSurgerys PS WHERE PS.DatePost3 <= DATEADD(day,5,GETDATE()) AND PS.DatePost3 >= GETDATE() AND NOT EXISTS ( SELECT * FROM Notifications N WHERE N.PeopleSurgeryID = PS.PeopleSurgeryID AND N.Origin = 'datepost3')");
        $datepost4 = DB::select("SELECT * FROM PeopleSurgerys PS WHERE PS.DatePost4 <= DATEADD(day,5,GETDATE()) AND PS.DatePost4 >= GETDATE() AND NOT EXISTS ( SELECT * FROM Notifications N WHERE N.PeopleSurgeryID = PS.PeopleSurgeryID AND N.Origin = 'datepost4')");
        $datepost5 = DB::select("SELECT * FROM PeopleSurgerys PS WHERE PS.DatePost5 <= DATEADD(day,5,GETDATE()) AND PS.DatePost5 >= GETDATE() AND NOT EXISTS ( SELECT * FROM Notifications N WHERE N.PeopleSurgeryID = PS.PeopleSurgeryID AND N.Origin = 'datepost5')");
        $datepost6 = DB::select("SELECT * FROM PeopleSurgerys PS WHERE PS.DatePost6 <= DATEADD(day,5,GETDATE()) AND PS.DatePost6 >= GETDATE() AND NOT EXISTS ( SELECT * FROM Notifications N WHERE N.PeopleSurgeryID = PS.PeopleSurgeryID AND N.Origin = 'datepost6')");
        
        foreach ($surgerys as $s) {
            $n = new Notification();
            $n->PeopleID = $s->PeopleID;
            $n->Origin = "surgery";
            $n->PeopleSurgeryID = $s->PeopleSurgeryID;
            $n->Description = "Próxima cirugia programada";
            $n->CreatedAt = date("Y-m-d H:i:s");
            $n->WhenAt = $s->DateAsSurgery;            
            $n->save();
        }
        foreach ($finish as $s) {
            $n = new Notification();
            $n->PeopleID = $s->PeopleID;
            $n->Origin = "finish";
            $n->PeopleSurgeryID = $s->PeopleSurgeryID;
            $n->Description = "Próximo vencimiento examenes";
            $n->CreatedAt = date("Y-m-d H:i:s");
            $n->WhenAt = $s->DateAsFinish;
            $n->save();
        }

        foreach ($enter as $s) {
            $n = new Notification();
            $n->PeopleID = $s->PeopleID;
            $n->Origin = "enter";
            $n->PeopleSurgeryID = $s->PeopleSurgeryID;
            $n->Description = "Próximo PAD";
            $n->CreatedAt = date("Y-m-d H:i:s");
            $n->WhenAt = $s->DateAsEnter;
            $n->save();
        }
        foreach ($datepost1 as $s) {
            $n = new Notification();
            $n->PeopleID = $s->PeopleID;
            $n->Origin = "datepost1";
            $n->PeopleSurgeryID = $s->PeopleSurgeryID;
            $n->Description = "Fecha Control Cirujano";
            $n->CreatedAt = date("Y-m-d H:i:s");
            $n->WhenAt = $s->DatePost1;
            $n->save();
        }
        foreach ($datepost2 as $s) {
            $n = new Notification();
            $n->PeopleID = $s->PeopleID;
            $n->Origin = "datepost2";
            $n->PeopleSurgeryID = $s->PeopleSurgeryID;
            $n->Description = "Fecha Control 6 Meses";
            $n->CreatedAt = date("Y-m-d H:i:s");
            $n->WhenAt = $s->DatePost2;
            $n->save();
        }
        foreach ($datepost3 as $s) {
            $n = new Notification();
            $n->PeopleID = $s->PeopleID;
            $n->Origin = "datepost3";
            $n->PeopleSurgeryID = $s->PeopleSurgeryID;
            $n->Description = "Fecha Control 1 Año";
            $n->CreatedAt = date("Y-m-d H:i:s");
            $n->WhenAt = $s->DatePost3;
            $n->save();
        }
        foreach ($datepost4 as $s) {
            $n = new Notification();
            $n->PeopleID = $s->PeopleID;
            $n->Origin = "datepost4";
            $n->PeopleSurgeryID = $s->PeopleSurgeryID;
            $n->Description = "Control Nutriología";
            $n->CreatedAt = date("Y-m-d H:i:s");
            $n->WhenAt = $s->DatePost4;
            $n->save();
        }
        foreach ($datepost5 as $s) {
            $n = new Notification();
            $n->PeopleID = $s->PeopleID;
            $n->Origin = "datepost5";
            $n->PeopleSurgeryID = $s->PeopleSurgeryID;
            $n->Description = "Control Nutricionista";
            $n->CreatedAt = date("Y-m-d H:i:s");
            $n->WhenAt = $s->DatePost5;
            $n->save();
        }
        foreach ($datepost6 as $s) {
            $n = new Notification();
            $n->PeopleID = $s->PeopleID;
            $n->Origin = "datepost6";
            $n->PeopleSurgeryID = $s->PeopleSurgeryID;
            $n->Description = "Control Psicológico";
            $n->CreatedAt = date("Y-m-d H:i:s");
            $n->WhenAt = $s->DatePost6;
            $n->save();
        }
        return response()->json([
            "success" => true
        ], 200);
    }
}
