<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsUser extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'smsuser';
    protected $fillable = [
        'id' , 'user_id' , 'content' , 'object_type' , 'object_id' , 'created_at'
    ];
}
