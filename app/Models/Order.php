<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    protected $table = 'Orders';
    protected $primaryKey = 'OrderID';
    public $timestamps = false;
    protected $guarded = [];
}
