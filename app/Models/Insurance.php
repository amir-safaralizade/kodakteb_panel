<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'id','name','created_at'
    ];
    use HasFactory;
}
