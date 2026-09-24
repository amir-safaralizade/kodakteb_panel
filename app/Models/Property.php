<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'property';
    protected $fillable = [
        'id' , 'subject' , 'value1' , 'value2' , 'data'
    ];
}
