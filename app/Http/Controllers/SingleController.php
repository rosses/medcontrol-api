<?php

namespace App\Http\Controllers;

use App\Models\GroupSingle;
use App\Models\Order;
use App\Models\Medicine;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class SingleController extends Controller
{
    public function saveOrder(Request $request) {
        try {
            $g = new GroupSingle();
            $g->Type='order';
            $g->PeopleID=$request->PeopleID;
            $g->CreatedUserID = JWTAuth::user()->UserID;
            $g->CreatedAt = date("Y-m-d H:i:s");
            $g->save();

            foreach ($request->data as $o) {
                $order = new Order();
                $order->ExamID = $o["ExamID"];
                $order->Description = $o["Description"];
                $order->DateID = 0;
                $order->PeopleID = $request->PeopleID;
                $order->CreatedUserID = JWTAuth::user()->UserID;
                $order->CreatedAt = date("Y-m-d H:i:s");
                $order->GroupSingleID = $g->GroupSingleID;
                $order->save();
            }
            return response()->json([
                "GroupSingleID" => $g->GroupSingleID
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }
    }
    public function saveRecipe(Request $request) {
        try {
            $g = new GroupSingle();
            $g->Type='recipe';
            $g->PeopleID=$request->PeopleID;
            $g->CreatedUserID = JWTAuth::user()->UserID;
            $g->CreatedAt = date("Y-m-d H:i:s");
            $g->save();

            foreach ($request->data as $r) {
                $recipe = new Recipe();
                if ($r["MedicineID"] == "99999999") {
                    $dd = new Medicine();
                    $dd->Name = (isset($r["MedicineNew"]) ? $r["MedicineNew"] : '');
                    $dd->LabID=1;
                    $dd->Active=1;
                    $dd->save();
                    $MedicineID = $dd->MedicineID;
                } else {
                    $MedicineID = $r["MedicineID"];
                }
                $recipe->MedicineID = $MedicineID;
                $recipe->Dose = $r["Dose"];
                $recipe->Period = $r["Period"];
                $recipe->Periodicity = $r["Periodicity"];
                $recipe->DateID = 0;
                $recipe->PeopleID = $request->PeopleID;
                $recipe->CreatedUserID = JWTAuth::user()->UserID;
                $recipe->CreatedAt = date("Y-m-d H:i:s");
                $recipe->GroupSingleID = $g->GroupSingleID;
                $recipe->save();
            }
            return response()->json([
                "GroupSingleID" => $g->GroupSingleID
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }
    }
    
    public function deleteOrder($id) {       
        Order::where("GroupSingleID",$id)->delete();
        GroupSingle::find($id)->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
    public function deleteRecipe($id) {       
        Recipe::where("GroupSingleID",$id)->delete();
        GroupSingle::find($id)->delete();
        return response()->json([
            "success" => true
        ], 200);
    }
}
