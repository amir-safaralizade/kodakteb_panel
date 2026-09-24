<?php

namespace App\Http\Requests;

use App\services\ClinicInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => ClinicInput::digits($this->input('phone')),
            'jalali_month' => ClinicInput::digits($this->input('jalali_month')),
            'jalali_day' => ClinicInput::digits($this->input('jalali_day')),
            'appointment_time' => ClinicInput::digits($this->input('appointment_time')),
        ]);
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'regex:/^09\d{9}$/'],
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_last_name' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'jalali_month' => ['required', 'integer', 'between:1,12'],
            'jalali_day' => ['required', 'integer', 'between:1,31'],
            'appointment_time' => ['required', 'date_format:H:i', function ($attribute, $value, $fail) {
                if ($value < '11:00' || $value > '21:00' || ! preg_match('/^(1[1-9]|20):(?:00|30)$|^21:00$/', $value)) {
                    $fail('ساعت نوبت باید بین ۱۱:۰۰ تا ۲۱:۰۰ و در بازه‌های نیم‌ساعته باشد.');
                }
            }],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', Rule::in(['scheduled', 'completed', 'cancelled'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $today = Jalalian::now();
            $month = (int) $this->input('jalali_month');
            $day = (int) $this->input('jalali_day');
            $year = $month < $today->getMonth() ? $today->getYear() + 1 : $today->getYear();
            if ($month && $day && ! CalendarUtils::checkDate($year, $month, $day)) {
                $validator->errors()->add('jalali_day', 'روز انتخاب‌شده برای این ماه معتبر نیست.');
            }

            if ($this->filled('user_id')) {
                $matches = \App\Models\User::whereKey($this->input('user_id'))
                    ->where('phone', $this->input('phone'))->exists();
                if (! $matches) {
                    $validator->errors()->add('user_id', 'پرونده انتخاب‌شده با شماره همراه واردشده مطابقت ندارد.');
                }
            } elseif (preg_match('/^09\d{9}$/', (string) $this->input('phone'))
                && \App\Models\User::where('phone', $this->input('phone'))->exists()) {
                $validator->errors()->add('user_id', 'برای این شماره پرونده موجود است؛ یکی از بیماران پیشنهادی را انتخاب کنید.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'شماره همراه را وارد کنید.',
            'phone.regex' => 'شماره همراه باید با 09 شروع شود و ۱۱ رقم باشد.',
            'patient_name.required' => 'نام مراجعه‌کننده را وارد کنید.',
            'appointment_time.required' => 'ساعت نوبت را انتخاب کنید.',
            'appointment_time.date_format' => 'ساعت نوبت معتبر نیست.',
            'jalali_month.*' => 'ماه شمسی معتبر انتخاب کنید.',
            'jalali_day.*' => 'روز شمسی معتبر انتخاب کنید.',
        ];
    }
}
