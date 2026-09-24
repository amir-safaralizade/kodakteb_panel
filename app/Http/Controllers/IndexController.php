<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;

class IndexController extends Controller
{
    public function dashbord()
    {
        $today = Visit::where('created_at', '>=', Carbon::today('Asia/Tehran'));

        return view('dashbord', [
            'patientCount' => User::count(),
            'todayCount' => (clone $today)->count(),
            'todayIncome' => (clone $today)->sum('hazine'),
            'recentVisits' => Visit::with(['user', 'insurance'])->latest('id')->limit(6)->get(),
            'todayVisits' => Visit::with(['user', 'insurance'])
                ->where('created_at', '>=', Carbon::today('Asia/Tehran'))
                ->orderBy('created_at')->get(),
            'todayAppointments' => Appointment::with('user')
                ->whereDate('appointment_date', Carbon::today('Asia/Tehran'))
                ->where('status', 'scheduled')
                ->orderBy('appointment_time')->get(),
        ]);
    }
}
