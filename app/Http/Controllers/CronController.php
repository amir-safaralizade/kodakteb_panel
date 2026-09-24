<?php

namespace App\Http\Controllers;

use App\Http\Controllers\front\FrontController;
use App\Models\AdminLog;
use App\Models\Appointment;
use App\Models\SmsUser;
use App\Models\User;
use App\Models\Visit;
use App\MyHelpers\MyJalaliDate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CronController extends Controller
{
    public function appointmentReminders(Request $request)
    {
        $configuredToken = (string) config('properties.cronToken');
        $receivedToken = (string) ($request->bearerToken() ?: $request->query('token', ''));
        if ($configuredToken === '') {
            return response()->json(['ok' => false, 'message' => 'CRON_TOKEN is not configured.'], 503);
        }
        if ($receivedToken === '' || ! hash_equals($configuredToken, $receivedToken)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized.'], 401);
        }

        $now = Carbon::now('Asia/Tehran');
        $dueQuery = Appointment::query()
            ->where('status', 'scheduled')
            ->whereNull('reminder_sent_at')
            ->whereNotNull('reminder_at')
            ->where('reminder_at', '<=', $now)
            ->where('reminder_attempts', '<', 5)
            ->whereRaw('TIMESTAMP(appointment_date, appointment_time) > ?', [$now->format('Y-m-d H:i:s')])
            ->where(function ($query) use ($now) {
                $query->whereNull('reminder_last_attempt_at')
                    ->orWhere('reminder_last_attempt_at', '<=', $now->copy()->subMinutes(5));
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('reminder_locked_at')
                    ->orWhere('reminder_locked_at', '<=', $now->copy()->subMinutes(10));
            });

        if (! filter_var(config('properties.appointmentReminderEnabled'), FILTER_VALIDATE_BOOL)) {
            return response()->json([
                'ok' => true,
                'enabled' => false,
                'due' => (clone $dueQuery)->count(),
                'message' => 'Dry run: connect the SMS provider, then enable APPOINTMENT_REMINDER_ENABLED.',
            ]);
        }

        $ids = (clone $dueQuery)->orderBy('reminder_at')->limit(50)->pluck('id');
        $sent = 0;
        $failed = 0;

        foreach ($ids as $id) {
            $appointment = DB::transaction(function () use ($id, $now) {
                $item = Appointment::query()->lockForUpdate()->find($id);
                if (! $item || $item->reminder_sent_at || $item->status !== 'scheduled'
                    || ! $item->reminder_at || $item->reminder_at->isAfter($now)
                    || ($item->reminder_locked_at && $item->reminder_locked_at->isAfter($now->copy()->subMinutes(10)))) {
                    return null;
                }

                $item->forceFill([
                    'reminder_locked_at' => $now,
                    'reminder_last_attempt_at' => $now,
                    'reminder_attempts' => $item->reminder_attempts + 1,
                ])->save();

                return $item;
            });

            if (! $appointment) {
                continue;
            }

            try {
                $result = $this->sendAppointmentReminder($appointment);
                if ($result['success']) {
                    $appointment->forceFill([
                        'reminder_sent_at' => Carbon::now('Asia/Tehran'),
                        'reminder_locked_at' => null,
                        'reminder_error' => null,
                    ])->save();
                    $sent++;
                } else {
                    $appointment->forceFill([
                        'reminder_locked_at' => null,
                        'reminder_error' => mb_substr((string) ($result['error'] ?? 'SMS provider rejected the request.'), 0, 500),
                    ])->save();
                    $failed++;
                }
            } catch (Throwable $exception) {
                $appointment->forceFill([
                    'reminder_locked_at' => null,
                    'reminder_error' => mb_substr($exception->getMessage(), 0, 500),
                ])->save();
                Log::error('Appointment reminder failed.', ['appointment_id' => $appointment->id, 'exception' => $exception]);
                $failed++;
            }
        }

        return response()->json(['ok' => true, 'enabled' => true, 'checked' => $ids->count(), 'sent' => $sent, 'failed' => $failed]);
    }

    private function sendAppointmentReminder(Appointment $appointment): array
    {
        $weekDays = [
            Carbon::SATURDAY => 'شنبه',
            Carbon::SUNDAY => 'یکشنبه',
            Carbon::MONDAY => 'دوشنبه',
            Carbon::TUESDAY => 'سه‌شنبه',
            Carbon::WEDNESDAY => 'چهارشنبه',
            Carbon::THURSDAY => 'پنجشنبه',
            Carbon::FRIDAY => 'جمعه',
        ];
        $weekDay = preg_replace('/[\s\x{200c}\x{200d}]+/u', '', $weekDays[$appointment->appointment_date->dayOfWeek]);
        $payload = [
            'mobile' => $appointment->phone,
            'templateId' => 579767,
            'Parameters' => [
                ['name' => 'WEEK_DAY', 'value' => $weekDay],
                ['name' => 'HOUR', 'value' => substr($appointment->appointment_time, 0, 5)],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'text/plain',
                'x-api-key' => config('properties.smsIrApiKey'),
            ])->timeout(15)->post(config('properties.smsIrVerifyUrl'), $payload);
            $responseData = $response->json();

            if (! $response->successful() || (int) ($responseData['status'] ?? 0) !== 1) {
                return [
                    'success' => false,
                    'error' => (string) ($responseData['message'] ?? 'پاسخ ناموفق از سرویس پیامک دریافت شد.'),
                ];
            }

            // This is only an audit log; failure to write it must not cause a duplicate SMS retry.
            try {
                SmsUser::create([
                    'user_id' => $appointment->user_id,
                    'content' => 'appointmentReminder',
                    'object_type' => Appointment::class,
                    'object_id' => $appointment->id,
                    'created_at' => Carbon::now('Asia/Tehran'),
                ]);
            } catch (Throwable $exception) {
                Log::warning('Appointment SMS was sent but its SmsUser audit log failed.', [
                    'appointment_id' => $appointment->id,
                    'error' => $exception->getMessage(),
                ]);
            }

            return ['success' => true];
        } catch (Throwable $exception) {
            return ['success' => false, 'error' => $exception->getMessage()];
        }
    }

    public function cron1(Request $request)
    {
        $users = User::orderby('id', 'asc')->get()->skip(6500)->take(500);
        $arr = [];
        foreach ($users as $user) {
            $visit = Visit::where('user_id', $user->id)->orderby('id', 'desc')->first();
            if ($visit) {
                if ($visit->created_at > Carbon::parse('2024-06-01 00:00:00') && $visit->created_at < Carbon::parse('2025-12-19 00:00:00')) {
                    if (! empty($user->phone) && ! in_array($user->phone, $arr) && strlen($user->phone) == 11) {
                        $arr[] = $user->phone;
                    }
                }
            }
        }
        echo implode('<br>,', $arr);

        return;
        if (Carbon::now('asia/tehran') >= Carbon::today('asia/tehran')->addHours(21)->addMinutes(15)) {
            $end = Carbon::today('asia/tehran')->endOfDay();
            $log = AdminLog::where('ip', config('properties.wepip'))->where('data1', 'cron2')->where('data2', $end)->exists();
            if (! $log) {
                $log = AdminLog::create([
                    'ip' => config('properties.wepip'),
                    'data1' => 'cron2',
                    'data2' => $end,
                    'created_at' => Carbon::now('asia/tehran'),
                ]);
                $this->sendStatisicData();

                $users = User::where('created_at', '>', Carbon::today('asia/tehran')->startOfDay())
                    ->where('created_at', '<', Carbon::today('asia/tehran')->endOfDay())->get();

                $f = new FrontController;
                foreach ($users as $user) {
                    $f->sendprofilesms($user);
                }
            }

            return 'done';
        }
    }

    public function sendStatisicData()
    {
        $phones = [
            '09143046229',
            '09054089235',
        ];

        $jalali = new MyJalaliDate;
        $DATE = $jalali->georgianToJalali(Carbon::today('asia/tehran')->format('Y-m-d'));
        $VISITCOUNT = Visit::where('created_at', '>=', Carbon::today('asia/tehran'))->count('id');
        if ($VISITCOUNT == 0) {
            return 'ok';
        }
        $total_income = Visit::where('created_at', '>=', Carbon::today('asia/tehran'))->sum('hazine');
        $today_numeric_income = $total_income;
        $total_income = number_format(floor($total_income / 10));
        $SMSCOUNT = SmsUser::where('created_at', '>=', Carbon::today('asia/tehran'))->count('id');
        $NEWUSER = User::where('created_at', '>=', Carbon::today('asia/tehran'))->count('id');

        $sub = 1;
        $last_ctive_day = Carbon::today('asia/tehran')->subDays($sub);
        while (Visit::where('created_at', '>=', $last_ctive_day)->where('created_at', '<', Carbon::today('asia/tehran'))->doesntExist()) {
            $sub++;
            $last_ctive_day = Carbon::today('asia/tehran')->subDays($sub);
        }

        $start = Carbon::parse($last_ctive_day)->startOfDay();
        $end = Carbon::parse($last_ctive_day)->endOfDay();
        $last_total_income = Visit::whereBetween('created_at', [$start, $end])->sum('hazine');
        $last_numeric_income = $last_total_income;
        $last_total_income = number_format(floor($last_total_income / 10));
        if ($today_numeric_income == $last_numeric_income) {
            $STATUS = 'برابر';
        } elseif ($today_numeric_income < $last_numeric_income) {
            $STATUS = 'کاهشی';
        } elseif ($today_numeric_income > $last_numeric_income) {
            $STATUS = 'افزایشی';
        }

        foreach ($phones as $mobile) {
            $templateId = 133548;
            $params = [
                [
                    'name' => 'DATE',
                    'value' => $DATE,
                ],
                [
                    'name' => 'VISITCOUNT',
                    'value' => $VISITCOUNT,
                ],
                [
                    'name' => 'INCOME',
                    'value' => $total_income,
                ],
                [
                    'name' => 'STATUS',
                    'value' => $STATUS,
                ],
                [
                    'name' => 'NEWUSER',
                    'value' => $NEWUSER,
                ],
                [
                    'name' => 'SMSCOUNT',
                    'value' => $SMSCOUNT,
                ],
            ];
            $API_KEY = config('properties.smsIrApiKey');
            $url = config('properties.smsIrVerifyUrl');
            $mobile = $mobile;

            $header = [
                'Content-Type' => 'application/json',
                'Accept' => 'text/plain',
                'x-api-key' => $API_KEY,
            ];
            $data = [
                'mobile' => $mobile,
                'templateId' => $templateId,
                'Parameters' => $params,
            ];

            try {
                Http::withHeaders($header)->post($url, $data);
            } catch (Exception $e) {
                Log::info('sendsmseror:'.$e->getmessage());
                $message = '(وضعیت ارسال پیامک : خطا در ارتباط با سرور پیامک)';
            }
        }
    }
}
