<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BudgetStatus extends Model {
    protected $table = 'BudgetStatus';
    protected $primaryKey = 'BudgetStatusID';
    public $timestamps = false;
    protected $guarded = [];
}
