<?php

namespace App\Http\Requests;

use App\services\ClinicInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VisitFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['hazine' => ClinicInput::money($this->input('hazine'))]);
    }

    public function rules(): array
    {
        $rules = [
            'hazine' => ['required', 'numeric', 'min:0'],
            'insurance_id' => ['nullable', 'integer', 'exists:insurances,id'],
            'raveshdaryaft' => ['nullable', Rule::in(['کارت', 'نقدی', 'ک ب ک'])],
        ];
        foreach (['elat', 'alaem', 'tashkhis', 'plan', 'tozihat'] as $field) {
            $rules[$field] = ['nullable', 'string'];
        }

        return $rules;
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
