<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'user_id', 'created_by', 'patient_name', 'phone', 'appointment_date',
        'jalali_year', 'jalali_month', 'jalali_day', 'appointment_time', 'status', 'notes',
        'reminder_at', 'reminder_sent_at', 'reminder_locked_at', 'reminder_last_attempt_at',
        'reminder_attempts', 'reminder_error',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'reminder_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'reminder_locked_at' => 'datetime',
        'reminder_last_attempt_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
