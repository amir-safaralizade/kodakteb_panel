<?php

namespace App\Http\Requests;

use App\services\ClinicInput;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Morilog\Jalali\CalendarUtils;

class VisitFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['hazine' => ClinicInput::money($this->input('hazine'))]);
        $this->merge([
            'follow_up_month' => ClinicInput::digits($this->input('follow_up_month')),
            'follow_up_day' => ClinicInput::digits($this->input('follow_up_day')),
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'hazine' => ['required', 'numeric', 'min:0'],
            'insurance_id' => ['nullable', 'integer', 'exists:insurances,id'],
            'raveshdaryaft' => ['nullable', Rule::in(['کارت', 'نقدی', 'ک ب ک'])],
            'follow_up_period' => ['nullable', Rule::in(['3', '7', '14', '30', 'custom'])],
            'follow_up_month' => ['nullable', 'required_if:follow_up_period,custom', 'integer', 'between:1,12'],
            'follow_up_day' => ['nullable', 'required_if:follow_up_period,custom', 'integer', 'between:1,31'],
            'follow_up_notes' => ['nullable', 'string', 'max:1000'],
        ];
        foreach (['elat', 'alaem', 'tashkhis', 'plan', 'tozihat'] as $field) {
            $rules[$field] = ['nullable', 'string'];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('follow_up_period') !== 'custom') {
                return;
            }
            $todayJalali = \Morilog\Jalali\Jalalian::now();
            $month = (int) $this->input('follow_up_month');
            $day = (int) $this->input('follow_up_day');
            $year = $month < $todayJalali->getMonth() ? $todayJalali->getYear() + 1 : $todayJalali->getYear();
            if (! CalendarUtils::checkDate($year, $month, $day)) {
                $validator->errors()->add('follow_up_day', 'روز انتخاب‌شده برای این ماه معتبر نیست.');

                return;
            }
            $gregorian = CalendarUtils::toGregorian($year, $month, $day);
            $dueDate = Carbon::create($gregorian[0], $gregorian[1], $gregorian[2], 0, 0, 0, 'Asia/Tehran');
            if ($dueDate->lessThanOrEqualTo(Carbon::today('Asia/Tehran'))) {
                $validator->errors()->add('follow_up_day', 'تاریخ ویزیت مجدد باید بعد از امروز باشد.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'hazine.required' => 'مبلغ دریافتی را به ریال وارد کنید؛ برای ویزیت رایگان صفر بنویسید.',
            'hazine.numeric' => 'مبلغ باید عدد باشد؛ مثلاً 1,500,000 ریال.',
            'hazine.min' => 'مبلغ نمی‌تواند منفی باشد.',
            'insurance_id.exists' => 'بیمه انتخاب‌شده در دسترس نیست؛ دوباره انتخاب کنید.',
            'raveshdaryaft.in' => 'روش دریافت را از گزینه‌های موجود انتخاب کنید.',
        ];
    }
}
