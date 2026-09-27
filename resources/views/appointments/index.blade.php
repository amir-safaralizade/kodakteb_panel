@extends('layouts.dashbord')
@section('header_title', 'نوبت‌دهی')

@section('content')
<link rel="stylesheet" href="{{ asset('matabgaleb/appointments.css') }}?v=9">

<div class="appointment-heading">
    <div><h1 class="page-title">نوبت‌دهی</h1><p>ثبت سریع نوبت با تاریخ شمسی و اتصال خودکار به پرونده بیمار</p></div>
    <a class="btn btn-outline-primary" href="https://www.time.ir/" target="_blank" rel="noopener noreferrer">تقویم و تعطیلات رسمی ↗</a>
</div>

<section class="appointment-card" aria-labelledby="new-appointment-title">
    <div class="appointment-card__title"><span aria-hidden="true">＋</span><div><h2 id="new-appointment-title">نوبت جدید</h2><small>شماره همراه را وارد کنید؛ پرونده موجود به‌صورت خودکار پیشنهاد می‌شود.</small></div></div>
    <form method="post" action="{{ route('appointments.store') }}" id="appointment-form" data-patient-url="{{ route('appointments.patient') }}" data-month-days='@json($monthDays)' data-month-weekdays='@json($monthWeekdays)' data-today-month="{{ \Morilog\Jalali\Jalalian::now()->getMonth() }}" data-today-day="{{ \Morilog\Jalali\Jalalian::now()->getDay() }}">
        @csrf
        <input type="hidden" name="user_id" id="appointment-user-id" value="{{ old('user_id') }}">
        <div class="appointment-fields">
            <div class="appointment-field">
                <label for="appointment-phone">شماره همراه <span>*</span></label>
                <input class="form-control" id="appointment-phone" name="phone" inputmode="numeric" autocomplete="off" maxlength="11" placeholder="09123456789" value="{{ old('phone') }}" required>
                <small id="patient-search-status" role="status" aria-live="polite">با ورود حداقل ۴ رقم، پرونده‌های مرتبط پیشنهاد می‌شوند.</small>
            </div>
            <div class="appointment-field">
                <label for="appointment-name">نام بیمار <span>*</span></label>
                <input class="form-control" id="appointment-name" name="patient_name" maxlength="255" placeholder="نام" value="{{ old('patient_name') }}" required>
            </div>
            <div class="appointment-field">
                <label for="appointment-last-name">نام خانوادگی</label>
                <input class="form-control" id="appointment-last-name" name="patient_last_name" maxlength="255" placeholder="نام خانوادگی" value="{{ old('patient_last_name') }}">
            </div>
            <div class="appointment-field">
                <label for="appointment-month">ماه</label>
                <select class="form-control" id="appointment-month" name="jalali_month" required>
                    @foreach($months as $number => $name)
                        <option value="{{ $number }}" @selected((int) old('jalali_month', $selectedMonth) === $number)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="appointment-field">
                <label for="appointment-time">ساعت نوبت <span>*</span></label>
                <select class="form-control" id="appointment-time" name="appointment_time" required>
                    <option value="">انتخاب ساعت</option>
                    @for($hour = 11; $hour <= 21; $hour++)
                        @foreach(($hour === 21 ? [0] : [0, 30]) as $minute)
                            @php($time = sprintf('%02d:%02d', $hour, $minute))
                            <option value="{{ $time }}" @selected(old('appointment_time') === $time)>{{ $time }}</option>
                        @endforeach
                    @endfor
                </select>
            </div>
        </div>

        <div id="patient-match" class="patient-match" hidden>
            <span class="patient-match__icon" aria-hidden="true">✓</span>
            <div class="patient-match__content"><strong>پرونده موجود پیدا شد</strong><small>بیماری که نوبت برای اوست انتخاب کنید.</small><div id="patient-match-options" class="patient-match__options"></div></div>
        </div>

        <fieldset class="day-picker">
            <legend>انتخاب روز</legend>
            <div class="day-picker__hint">روز موردنظر را از تقویم انتخاب کنید.</div>
            <div class="day-grid" id="appointment-days"></div>
            <input type="hidden" id="appointment-day" name="jalali_day" value="{{ old('jalali_day') }}" required>
        </fieldset>

        <div class="appointment-field appointment-field--notes">
            <label for="appointment-notes">توضیحات <small>(اختیاری)</small></label>
            <textarea class="form-control" id="appointment-notes" name="notes" rows="2" maxlength="1000" placeholder="توضیحات کوتاه برای منشی…">{{ old('notes') }}</textarea>
        </div>
        <div class="appointment-submit"><button class="btn btn-primary" type="submit">ثبت نوبت</button></div>
    </form>
</section>

<section class="appointment-list">
    <div class="appointment-list__head">
        <div><h2>نوبت‌های {{ $months[$selectedMonth] }}</h2><span>{{ $items->total() }} نوبت</span></div>
        <form method="get" action="{{ route('appointments.index') }}">
            <label for="list-month">نمایش ماه</label>
            <select id="list-month" name="month" onchange="this.form.submit()">
                @foreach($months as $number => $name)<option value="{{ $number }}" @selected($selectedMonth === $number)>{{ $name }}</option>@endforeach
            </select>
        </form>
    </div>
    <div class="table-scroll" tabindex="0" role="region" aria-label="فهرست نوبت‌ها">
        <table><thead><tr><th>روز</th><th>ساعت</th><th>مراجعه‌کننده</th><th>شماره همراه</th><th>پرونده</th><th>یادآوری پیامکی</th><th>توضیحات</th><th>عملیات</th></tr></thead>
        <tbody>
        @forelse($items as $item)
            <tr>
                <td><span class="appointment-day-badge">{{ $item->jalali_day }}</span></td>
                <td class="appointment-time">{{ substr($item->appointment_time, 0, 5) }}</td>
                <td>{{ $item->patient_name }}@if($item->user?->Age())<small class="patient-age">{{ $item->user->Age() }}</small>@endif</td><td dir="ltr">{{ $item->phone }}</td>
                <td>@if($item->user)<a class="patient-file-link" href="{{ route('user.show', $item->user_id) }}">پرونده {{ $item->user->caseNumber }}</a>@else<span class="standalone-badge">بدون پرونده</span>@endif</td>
                <td>
                    @if($item->reminder_sent_at)
                        <span class="reminder-badge reminder-badge--sent" title="{{ $item->reminder_sent_at->format('Y-m-d H:i') }}">✓ ارسال شده</span>
                    @elseif($item->status !== 'scheduled')
                        <span class="reminder-badge reminder-badge--muted">ارسال نمی‌شود</span>
                    @elseif(!$item->reminder_at)
                        <span class="reminder-badge reminder-badge--muted">بدون یادآوری</span>
                    @elseif($item->reminder_attempts > 0 && $item->reminder_error)
                        <span class="reminder-badge reminder-badge--failed" title="{{ $item->reminder_error }}">! ناموفق</span>
                    @else
                        <span class="reminder-badge reminder-badge--pending">◷ در انتظار</span>
                    @endif
                </td>
                <td class="appointment-notes">{{ $item->notes ?: '—' }}</td>
                <td><div class="appointment-actions"><a class="appointment-edit" href="{{ route('appointments.edit', $item) }}" aria-label="ویرایش نوبت">✎</a><form method="post" action="{{ route('appointments.destroy', $item) }}">@csrf @method('DELETE')<button class="appointment-delete" type="submit" onclick="return confirm('این نوبت حذف شود؟')" aria-label="حذف نوبت">×</button></form></div></td>
            </tr>
        @empty
            <tr><td colspan="8"><div class="empty-state"><span aria-hidden="true">▦</span><h3>نوبتی برای این ماه ثبت نشده</h3><p>اولین نوبت را از فرم بالا ثبت کنید.</p></div></td></tr>
        @endforelse
        </tbody></table>
    </div>
    <div class="mt-3">{{ $items->links('pagination::bootstrap-5') }}</div>
</section>
<script src="{{ asset('matabgaleb/appointments.js') }}?v=3" defer></script>
@endsection
