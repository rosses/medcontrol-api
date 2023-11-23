<?php

namespace App\Http\Controllers;

use App\Models\BudgetStatus;
use Illuminate\Http\Request;
class BudgetStatusController extends Controller
{
    public function index(Request $request) {
        $rows = BudgetStatus::select('BudgetStatus.*')->orderBy('BudgetStatus.Name','ASC');
        $rows = $rows->get();
        return response()->json($rows);
    }

   
}
