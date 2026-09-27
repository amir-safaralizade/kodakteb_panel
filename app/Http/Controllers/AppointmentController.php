<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentRequest;
use App\Models\Appointment;
use App\Models\User;
use App\MyHelpers\MyJalaliDate;
use App\services\ClinicInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

class AppointmentController extends Controller
{
    private const MONTHS = [1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];

    public function index(Request $request)
    {
        $today = Jalalian::now();
        $month = (int) ClinicInput::digits((string) $request->input('month', $today->getMonth()));
        if ($month < 1 || $month > 12) {
            $month = $today->getMonth();
        }

        $items = Appointment::with('user')
            ->where('jalali_year', $this->yearForMonth($month))
            ->where('jalali_month', $month)
            ->orderBy('jalali_day')->orderBy('appointment_time')->paginate(50)
            ->appends(['month' => $month]);

        return view('appointments.index', [
            'items' => $items,
            'months' => self::MONTHS,
            'selectedMonth' => $month,
            'monthDays' => collect(range(1, 12))->mapWithKeys(fn ($number) => [
                $number => (new Jalalian($this->yearForMonth($number), $number, 1))->getMonthDays(),
            ]),
            'monthWeekdays' => $this->monthWeekdays(),
        ]);
    }

    public function store(AppointmentRequest $request)
    {
        $data = $request->validated();
        $year = $this->yearForMonth((int) $data['jalali_month']);
        $gregorian = CalendarUtils::toGregorian($year, (int) $data['jalali_month'], (int) $data['jalali_day']);
        $appointmentDate = sprintf('%04d-%02d-%02d', ...$gregorian);
        $reminderAt = $this->reminderAt($appointmentDate, now('Asia/Tehran'));

        $createdPatient = false;
        DB::transaction(function () use ($data, $year, $appointmentDate, $reminderAt, &$createdPatient) {
            $user = isset($data['user_id']) ? User::findOrFail($data['user_id']) : null;

            if (! $user) {
                // Lock user creation so two receptionists cannot assign the same case number.
                $lastCaseNumber = (int) User::query()
                    ->lockForUpdate()
                    ->orderByRaw('CAST(caseNumber AS UNSIGNED) DESC')
                    ->value('caseNumber');

                $user = User::create([
                    'caseNumber' => $lastCaseNumber + 1,
                    'name' => $data['patient_name'],
                    'lastName' => $data['patient_last_name'] ?? null,
                    'phone' => $data['phone'],
                    'created_at' => now('Asia/Tehran'),
                ]);
                $createdPatient = true;
            }

            Appointment::create([
                'user_id' => $user->id,
                'created_by' => auth('admin')->id(),
                'patient_name' => trim($user->name.' '.$user->lastName),
                'phone' => $data['phone'],
                'appointment_date' => $appointmentDate,
                'jalali_year' => $year,
                'jalali_month' => $data['jalali_month'],
                'jalali_day' => $data['jalali_day'],
                'appointment_time' => $data['appointment_time'].':00',
                'status' => 'scheduled',
                'notes' => $data['notes'] ?? null,
                'reminder_at' => $reminderAt,
            ]);
        });

        return redirect()->route('appointments.index', ['month' => $data['jalali_month']])
            ->with('success', $createdPatient
                ? 'پرونده جدید ساخته شد و نوبت به آن متصل گردید.'
                : 'نوبت ثبت و به پرونده بیمار متصل شد.');
    }

    public function all(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $items = Appointment::with('user')
            ->when($from, fn ($query) => $query->whereDate('appointment_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('appointment_date', '<=', $to))
            ->orderByDesc('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(50)
            ->appends($request->only(['from', 'to']));

        return view('appointments.all', compact('items'));
    }

    public function findPatient(Request $request)
    {
        $phone = ClinicInput::digits((string) $request->query('phone'));
        if (! preg_match('/^09\d{2,9}$/', $phone)) {
            return response()->json(['found' => false, 'exact' => false, 'users' => []]);
        }

        $users = User::where('phone', 'like', $phone.'%')
            ->latest('id')
            ->limit(6)
            ->get(['id', 'name', 'lastName', 'caseNumber', 'phone']);
        if ($users->isEmpty()) {
            return response()->json(['found' => false, 'exact' => false, 'users' => []]);
        }

        return response()->json([
            'found' => true,
            'exact' => $users->contains(fn (User $user) => $user->phone === $phone),
            'users' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'first_name' => $user->name,
                'last_name' => $user->lastName,
                'name' => trim($user->name.' '.$user->lastName) ?: 'بیمار بدون نام',
                'case_number' => $user->caseNumber,
                'phone' => $user->phone,
                'url' => route('user.show', $user->id),
            ]),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function edit(Appointment $appointment)
    {
        return view('appointments.edit', [
            'appointment' => $appointment->load('user'),
            'months' => self::MONTHS,
            'monthDays' => collect(range(1, 12))->mapWithKeys(fn ($number) => [
                $number => (new Jalalian($this->yearForMonth($number), $number, 1))->getMonthDays(),
            ]),
            'monthWeekdays' => $this->monthWeekdays(),
        ]);
    }

    public function update(AppointmentRequest $request, Appointment $appointment)
    {
        $data = $request->validated();
        $year = $this->yearForMonth((int) $data['jalali_month']);
        $gregorian = CalendarUtils::toGregorian($year, (int) $data['jalali_month'], (int) $data['jalali_day']);
        $appointmentDate = sprintf('%04d-%02d-%02d', ...$gregorian);
        $status = $data['status'] ?? 'scheduled';
        $reminderAt = $appointment->reminder_sent_at || $status !== 'scheduled'
            ? $appointment->reminder_at
            : $this->reminderAt($appointmentDate, now('Asia/Tehran'));

        $appointment->update([
            'patient_name' => trim(($appointment->user?->name ?? $data['patient_name']).' '.($appointment->user?->lastName ?? ($data['patient_last_name'] ?? ''))),
            'phone' => $data['phone'],
            'appointment_date' => $appointmentDate,
            'jalali_year' => $year,
            'jalali_month' => $data['jalali_month'],
            'jalali_day' => $data['jalali_day'],
            'appointment_time' => $data['appointment_time'].':00',
            'status' => $status,
            'notes' => $data['notes'] ?? null,
            'reminder_at' => $reminderAt,
            'reminder_locked_at' => null,
            'reminder_attempts' => $appointment->reminder_sent_at ? $appointment->reminder_attempts : 0,
            'reminder_last_attempt_at' => $appointment->reminder_sent_at ? $appointment->reminder_last_attempt_at : null,
            'reminder_error' => null,
        ]);

        return redirect()->route('appointments.index', ['month' => $data['jalali_month']])
            ->with('success', 'نوبت با موفقیت ویرایش شد.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return back()->with('success', 'نوبت حذف شد.');
    }

    private function yearForMonth(int $month): int
    {
        $today = Jalalian::now();

        return $month < $today->getMonth() ? $today->getYear() + 1 : $today->getYear();
    }

    private function monthWeekdays()
    {
        $names = [0 => 'یکشنبه', 'دوشنبه', 'سهشنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'];

        return collect(range(1, 12))->mapWithKeys(function ($month) use ($names) {
            $year = $this->yearForMonth($month);
            $days = (new Jalalian($year, $month, 1))->getMonthDays();

            return [$month => collect(range(1, $days))->mapWithKeys(function ($day) use ($year, $month, $names) {
                $gregorian = CalendarUtils::toGregorian($year, $month, $day);
                $weekday = \Carbon\Carbon::create($gregorian[0], $gregorian[1], $gregorian[2], 0, 0, 0, 'Asia/Tehran')->dayOfWeek;

                return [$day => $names[$weekday]];
            })];
        });
    }

    private function reminderAt(string $appointmentDate, \Carbon\CarbonInterface $createdAt): ?\Carbon\CarbonInterface
    {
        $date = \Carbon\Carbon::parse($appointmentDate, 'Asia/Tehran')->startOfDay();

        // Same-day reservations are intentionally silent.
        if ($createdAt->isSameDay($date)) {
            return null;
        }

        // A reservation made yesterday is reminded at opening time today.
        if ($createdAt->copy()->startOfDay()->diffInDays($date) === 1) {
            return $date->copy()->setTime(8, 0);
        }

        // Earlier reservations are reminded on the previous evening.
        return $date->copy()->subDay()->setTime(18, 0);
    }

    private function dateRange(Request $request): array
    {
        $request->merge([
            'from' => ClinicInput::date($request->input('from')),
            'to' => ClinicInput::date($request->input('to')),
        ]);

        $validJalaliDate = function ($attribute, $value, $fail) {
            if (! preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', (string) $value, $parts)
                || ! CalendarUtils::checkDate((int) ($parts[1] ?? 0), (int) ($parts[2] ?? 0), (int) ($parts[3] ?? 0))) {
                $fail('تاریخ شمسی واردشده معتبر نیست.');
            }
        };

        $request->validate([
            'from' => ['nullable', 'string', $validJalaliDate],
            'to' => ['nullable', 'string', $validJalaliDate],
        ]);

        $jalali = new MyJalaliDate;
        $from = $request->filled('from') ? $jalali->jalaliToGeorgian($request->input('from')) : null;
        $to = $request->filled('to') ? $jalali->jalaliToGeorgian($request->input('to')) : null;

        if ($from && $to && $from > $to) {
            throw ValidationException::withMessages(['to' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.']);
        }

        return [$from, $to];
    }
}
