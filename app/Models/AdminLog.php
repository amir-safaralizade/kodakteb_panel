<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'id' , 'ip' , 'admin_id' , 'data1' , 'data2' , 'fulldata' ,'created_at'
    ];
    public $timestamps = false;
}
