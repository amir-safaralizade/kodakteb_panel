<?php

namespace App\Models;

use App\MyHelpers\MyJalaliDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $fillable = [
        'id', 'user_id', 'elat', 'alaem',
        'tashkhis', 'plan', 'insurance_id',
        'hazine', 'raveshdaryaft', 'tozihat',
        'created_at',
    ];

    public function insurance()
    {
        return $this->belongsTo(Insurance::class, 'insurance_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reminders()
    {
        return $this->hasMany(PatientReminder::class);
    }

    public function getCreateJAttribute()
    {
        if ($this->created_at) {
            $val = (new MyJalaliDate)->georgianToJalali($this->created_at);

            return $val;
        }

        return null;
    }

    public function getIsClinicallyCompletedAttribute(): bool
    {
        return collect([$this->elat, $this->alaem, $this->tashkhis, $this->plan])
            ->contains(fn ($value) => filled($value));
    }
}
