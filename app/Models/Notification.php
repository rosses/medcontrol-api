<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model {
    protected $table = 'Notifications';
    protected $primaryKey = 'NotifyID';
    public $timestamps = false;
    protected $guarded = [];
}
