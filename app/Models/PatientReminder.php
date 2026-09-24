<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientReminder extends Model
{
    protected $fillable = [
        'user_id', 'visit_id', 'created_by', 'type', 'due_date', 'jalali_date',
        'reminder_at', 'status', 'notes', 'sms_sent_at', 'sms_attempts',
        'sms_last_attempt_at', 'sms_locked_at', 'sms_error',
    ];

    protected $casts = [
        'due_date' => 'date',
        'reminder_at' => 'datetime',
        'sms_sent_at' => 'datetime',
        'sms_last_attempt_at' => 'datetime',
        'sms_locked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }
}
