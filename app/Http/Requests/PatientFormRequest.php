<?php

namespace App\Http\Requests;

use App\services\ClinicInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Morilog\Jalali\CalendarUtils;

class PatientFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        foreach (['caseNumber', 'phone', 'nationalCode'] as $field) {
            $this->merge([$field => ClinicInput::digits($this->input($field))]);
        }
        $this->merge(['birthday' => ClinicInput::date($this->input('birthday'))]);
    }

    public function rules(): array
    {
        $editing = $this->route('user') !== null;
        $rules = [
            'caseNumber' => ['required', 'integer', 'min:1', Rule::unique('users', 'caseNumber')->ignore($this->route('user'))],
            'name' => [$editing ? 'required' : 'nullable', 'string', 'max:255'],
            'phone' => [$editing ? 'required' : 'nullable', 'string', 'max:255'],
            'insurance' => ['nullable', 'integer', 'exists:insurances,id'],
            'sex' => ['nullable', Rule::in(['دختر', 'پسر'])],
            'birthday' => ['bail', 'nullable', 'string', function ($attribute, $value, $fail) {
                if (!preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $value, $parts)
                    || (int) $parts[1] < 1 || (int) $parts[1] > 3177
                    || !CalendarUtils::checkDate((int) $parts[1], (int) $parts[2], (int) $parts[3])) {
                    $fail('تاریخ تولد شمسی معتبر وارد کنید؛ مثلاً 1400/01/25.');
                }
            }],
        ];
        foreach (['lastname', 'nationalCode', 'fatherName', 'motherName', 'motherLastName'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'caseNumber.unique' => 'این شماره پرونده قبلاً ثبت شده است؛ اطلاعات واردشده حفظ شده، شماره پرونده را بررسی کنید.',
            'name.required' => 'نام بیمار را وارد کنید.',
            'phone.required' => 'شماره همراه را وارد کنید.',
            'insurance.exists' => 'بیمه انتخاب‌شده در دسترس نیست؛ دوباره انتخاب کنید.',
            '*.string' => 'مقدار این فیلد باید متن باشد.',
            '*.max' => 'حداکثر ۲۵۵ کاراکتر وارد کنید.',
        ];
    }
}
