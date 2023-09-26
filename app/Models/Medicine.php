<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model {
    protected $table = 'Medicines';
    protected $primaryKey = 'MedicineID';
    public $timestamps = false;
    protected $guarded = [];
}
