<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TemplateOrder extends Model {
    protected $table = 'TemplateOrders';
    protected $primaryKey = 'TemplateOrderID';
    public $timestamps = false;
    protected $guarded = [];
}
