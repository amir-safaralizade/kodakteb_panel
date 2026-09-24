<?php

namespace App\Http\Controllers;

use App\Http\Requests\VisitFormRequest;
use App\Models\AdminLog;
use App\Models\PatientReminder;
use App\Models\SmsUser;
use App\Models\User;
use App\Models\Visit;
use App\MyHelpers\MyJalaliDate;
use App\services\ClinicInput;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;
use Throwable;

class VisitsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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

        $query = Visit::with(['user', 'insurance'])->orderBy('id', 'desc');
        $jalali = new MyJalaliDate;
        if ($request->filled('from') && $request->filled('to')
            && $jalali->jalaliToGeorgian($request->input('from')) > $jalali->jalaliToGeorgian($request->input('to'))) {
            throw ValidationException::withMessages(['to' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.']);
        }
        if ($request->filled('from')) {
            $query->where('created_at', '>=', Carbon::parse($jalali->jalaliToGeorgian($request->input('from')), 'Asia/Tehran')->startOfDay());
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', Carbon::parse($jalali->jalaliToGeorgian($request->input('to')), 'Asia/Tehran')->endOfDay());
        }

        $items = $query->paginate(50)->appends($request->only(['from', 'to']));

        return view('visits.index', compact('items'));
    }

    public function todaysVisit()
    {
        $items = Visit::with(['user', 'insurance'])->where('created_at', '>=', Carbon::today('asia/tehran'))->orderBy('created_at')->get();
        $toal_income = Visit::where('created_at', '>=', Carbon::today('asia/tehran'))->sum('hazine');
        $toal_count = Visit::where('created_at', '>=', Carbon::today('asia/tehran'))->count('id');
        $is_todaysVisit = true;

        return view('visits.index', compact('items', 'is_todaysVisit', 'toal_income', 'toal_count'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $id, Request $request)
    {
        $item = User::with(['insurance', 'visits.insurance', 'visits.reminders', 'reminders.visit'])->findOrfail($id);

        return view('visits.create', compact('item'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(string $id, VisitFormRequest $request)
    {
        $user = User::findOrFail($id);
        $price = $request->validated('hazine');
        Visit::create([
            'user_id' => $id,
            'elat' => $request['elat'],
            'alaem' => $request['alaem'],
            'tashkhis' => $request['tashkhis'],
            'plan' => $request['plan'],
            'insurance_id' => $request['insurance_id'],
            'hazine' => $price,
            'raveshdaryaft' => $request['raveshdaryaft'],
            'tozihat' => $request['tozihat'],
            'created_at' => Carbon::now('asia/tehran'),
        ]);

        session()->flash('success', 'ویزیت با موفقیت ثبت شد');

        return redirect()->route('visits.todaysvisit');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = Visit::with(['user.insurance', 'user.visits.insurance', 'user.visits.reminders', 'user.reminders.visit', 'reminders'])->findOrFail($id);
        $todayJalali = Jalalian::now();
        $followUpMonths = [1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
        $followUpMonthDays = collect(range(1, 12))->mapWithKeys(function ($month) use ($todayJalali) {
            $year = $month < $todayJalali->getMonth() ? $todayJalali->getYear() + 1 : $todayJalali->getYear();

            return [$month => (new Jalalian($year, $month, 1))->getMonthDays()];
        });
        $dayNames = [0 => 'یکشنبه', 'دوشنبه', 'سهشنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'];
        $followUpMonthWeekdays = collect(range(1, 12))->mapWithKeys(function ($month) use ($todayJalali, $dayNames, $followUpMonthDays) {
            $year = $month < $todayJalali->getMonth() ? $todayJalali->getYear() + 1 : $todayJalali->getYear();

            return [$month => collect(range(1, $followUpMonthDays[$month]))->mapWithKeys(function ($day) use ($year, $month, $dayNames) {
                $gregorian = CalendarUtils::toGregorian($year, $month, $day);
                $weekday = Carbon::create($gregorian[0], $gregorian[1], $gregorian[2], 0, 0, 0, 'Asia/Tehran')->dayOfWeek;

                return [$day => $dayNames[$weekday]];
            })];
        });

        return view('visits.edit', compact('item', 'followUpMonths', 'followUpMonthDays', 'followUpMonthWeekdays'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VisitFormRequest $request, string $id)
    {
        $item = Visit::findOrFail($id);
        $price = $request->validated('hazine');
        if ($request->filled('follow_up_period') && PatientReminder::where('visit_id', $item->id)->exists()) {
            throw ValidationException::withMessages(['follow_up_period' => 'برای این ویزیت قبلاً یادآور ثبت شده است.']);
        }

        $item->update([
            'elat' => $request['elat'],
            'alaem' => $request['alaem'],
            'tashkhis' => $request['tashkhis'],
            'plan' => $request['plan'],
            'insurance_id' => $request['insurance_id'],
            'hazine' => $price,
            'raveshdaryaft' => $request['raveshdaryaft'],
            'tozihat' => $request['tozihat'],
        ]);

        if ($request->filled('follow_up_period')) {
            if ($request->input('follow_up_period') === 'custom') {
                $todayJalali = Jalalian::now();
                $month = (int) $request->input('follow_up_month');
                $day = (int) $request->input('follow_up_day');
                $year = $month < $todayJalali->getMonth() ? $todayJalali->getYear() + 1 : $todayJalali->getYear();
                $gregorian = CalendarUtils::toGregorian($year, $month, $day);
                $dueDate = Carbon::create($gregorian[0], $gregorian[1], $gregorian[2], 0, 0, 0, 'Asia/Tehran');
            } else {
                $dueDate = Carbon::today('Asia/Tehran')->addDays((int) $request->input('follow_up_period'));
            }

            PatientReminder::create([
                'user_id' => $item->user_id,
                'visit_id' => $item->id,
                'created_by' => auth('admin')->id(),
                'type' => 'revisit',
                'due_date' => $dueDate,
                'jalali_date' => Jalalian::fromCarbon($dueDate)->format('Y/m/d'),
                'reminder_at' => $dueDate->copy()->subDay()->setTime(9, 0),
                'status' => 'pending',
                'notes' => $request->input('follow_up_notes'),
            ]);
        }

        AdminLog::create([
            'admin_id' => Auth()->user()->id,
            'data1' => json_encode($request->all()),
            'data2' => 'updatevisit',
            'created_at' => Carbon::now('asia/tehran'),
        ]);

        $result = '';
        if ($request->boolean('complete_visit')
            && isset($item->user->phone) && ! empty($item->user->phone)
            && $item->user->caseNumber) {
            $user = $item->user;
            $result = $this->sendNewVisitSMS($user->caseNumber, $user, $item->id);
        }

        session()->flash('success', 'عملیات با موفقیت انجام شد'.$result);

        return redirect()->route('visits.todaysvisit');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Visit::findOrFail($id);
        $item->delete();
        session()->flash('success', 'حذف  با موفقیت انجام شد');

        return redirect()->back();
    }

    public function completeReminder(PatientReminder $reminder)
    {
        $reminder->update(['status' => 'completed']);

        return back()->with('success', 'یادآور ویزیت مجدد انجام‌شده ثبت شد.');
    }

    public function destroyReminder(PatientReminder $reminder)
    {
        $reminder->update(['status' => 'cancelled']);

        return back()->with('success', 'یادآور ویزیت مجدد لغو شد.');
    }

    public function sendNewVisitSMS($caseNumber, User $user, $visitId)
    {
        $sendSms = SmsUser::where('object_type', Visit::class)->where('object_id', $visitId)->exists();
        if ($sendSms) {
            return '';
        }
        try {
            $API_KEY = config('properties.smsIrApiKey');
            $url = config('properties.smsIrVerifyUrl');
            $mobile = $user->phone;
            $templateId = 553868;
            $caseNumber = $caseNumber;
            $header = [
                'Content-Type' => 'application/json',
                'Accept' => 'text/plain',
                'x-api-key' => $API_KEY,
            ];
            $data = [
                'mobile' => $mobile,
                'templateId' => $templateId,
                'Parameters' => [
                    [
                        'name' => 'CASENUMBER',
                        'value' => $caseNumber,
                    ],
                ],
            ];

            $response = Http::withHeaders($header)->post($url, $data);
            $status = $response['status'];
            $CASENUMBER = $user->caseNumber;
            if ($status == 1) {
                SmsUser::create([
                    'user_id' => $user->id,
                    'content' => 'newVisit',
                    'object_type' => 'App\Models\Visit',
                    'object_id' => $visitId,
                    'created_at' => Carbon::now('asia/tehran'),
                ]);
                $message = '(پیامک ثبت ویزیت جدید با موفقیت ارسال شد)';
            } else {
                $message = '<span style="color:red">';
                $message = $message.'(';
                $message = $message.'وضعیت ارسال پیامک : '.$response['message'];
                $message = $message.')';
                $message = $message.'</span>';
            }

            return $message;
        } catch (Throwable $e) {
            $message = '(وضعیت ارسال پیامک : خطا در ارتباط با سرور پیامک)';
        }

        return $message ?? '';
    }
}
