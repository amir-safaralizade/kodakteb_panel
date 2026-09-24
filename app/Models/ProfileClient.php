<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileClient extends Model
{
    use HasFactory;

    protected $table = 'clients';
    protected $fillable = [
        'ip',
        'browser',
        'browser_version',
        'platform',
        'platform_version',
        'is_mobile',
        'is_desktop',
        'is_robot',
        'url'
    ];
}
