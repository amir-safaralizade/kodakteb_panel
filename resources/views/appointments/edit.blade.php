@extends('layouts.dashbord')
@section('header_title', 'ویرایش نوبت')

@section('content')
<link rel="stylesheet" href="{{ asset('matabgaleb/appointments.css') }}?v=4">
<div class="appointment-heading"><div><h1 class="page-title">ویرایش نوبت</h1><p>{{ $appointment->patient_name }} · {{ $appointment->phone }}</p></div><a class="btn btn-outline-primary" href="{{ route('appointments.index', ['month' => $appointment->jalali_month]) }}">بازگشت به نوبت‌ها</a></div>

<section class="appointment-card">
    <form method="post" action="{{ route('appointments.update', $appointment) }}" id="appointment-edit-form" data-month-days='@json($monthDays)' data-month-weekdays='@json($monthWeekdays)'>
        @csrf @method('PUT')
        <input type="hidden" name="user_id" value="{{ $appointment->user_id }}">
        <input type="hidden" name="phone" value="{{ $appointment->phone }}">
        <input type="hidden" name="patient_name" value="{{ $appointment->user?->name ?? $appointment->patient_name }}">
        <input type="hidden" name="patient_last_name" value="{{ $appointment->user?->lastName }}">
        <div class="edit-patient-summary"><span>بیمار</span><strong>{{ $appointment->patient_name }}</strong>@if($appointment->user?->Age())<small>سن: {{ $appointment->user->Age() }}</small>@endif<small dir="ltr">{{ $appointment->phone }}</small>@if($appointment->user)<a href="{{ route('user.show', $appointment->user_id) }}">مشاهده پرونده</a>@endif</div>
        <div class="appointment-fields appointment-fields--edit">
            <div class="appointment-field"><label for="edit-month">ماه</label><select class="form-control" id="edit-month" name="jalali_month" required>@foreach($months as $number => $name)<option value="{{ $number }}" @selected((int) old('jalali_month', $appointment->jalali_month) === $number)>{{ $name }}</option>@endforeach</select></div>
            <div class="appointment-field"><label for="edit-time">ساعت</label><select class="form-control" id="edit-time" name="appointment_time" required><option value="">انتخاب ساعت</option>@for($hour = 11; $hour <= 21; $hour++)@foreach(($hour === 21 ? [0] : [0, 30]) as $minute)@php($time = sprintf('%02d:%02d', $hour, $minute))<option value="{{ $time }}" @selected(old('appointment_time', substr($appointment->appointment_time, 0, 5)) === $time)>{{ $time }}</option>@endforeach @endfor</select></div>
            <div class="appointment-field"><label for="edit-status">وضعیت</label><select class="form-control" id="edit-status" name="status"><option value="scheduled" @selected(old('status', $appointment->status) === 'scheduled')>در انتظار مراجعه</option><option value="completed" @selected(old('status', $appointment->status) === 'completed')>انجام‌شده</option><option value="cancelled" @selected(old('status', $appointment->status) === 'cancelled')>لغوشده</option></select></div>
        </div>
        <fieldset class="day-picker"><legend>انتخاب روز</legend><div class="day-grid" id="edit-days"></div><input type="hidden" id="edit-day" name="jalali_day" value="{{ old('jalali_day', $appointment->jalali_day) }}" required></fieldset>
        <div class="appointment-field appointment-field--notes"><label for="edit-notes">توضیحات</label><textarea class="form-control" id="edit-notes" name="notes" rows="3" maxlength="1000">{{ old('notes', $appointment->notes) }}</textarea></div>
        <div class="appointment-submit"><button class="btn btn-primary" type="submit">ذخیره تغییرات</button></div>
    </form>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('appointment-edit-form'), month = document.getElementById('edit-month'), input = document.getElementById('edit-day'), grid = document.getElementById('edit-days'), counts = JSON.parse(form.dataset.monthDays || '{}'), weekdays = JSON.parse(form.dataset.monthWeekdays || '{}');
    function render() { const max = Number(counts[month.value] || 31); let selected = Number(input.value); if (selected > max) { selected = 0; input.value = ''; } grid.innerHTML = ''; for (let day = 1; day <= max; day++) { const weekday = weekdays[month.value]?.[day] || ''; const button = document.createElement('button'); button.type = 'button'; button.className = 'day-button' + (day === selected ? ' is-selected' : '') + (weekday === 'جمعه' ? ' is-friday' : ''); button.innerHTML = '<strong>' + day + '</strong><small>' + weekday + '</small>'; button.setAttribute('aria-label', 'روز ' + day + '، ' + weekday); button.setAttribute('aria-pressed', day === selected ? 'true' : 'false'); button.addEventListener('click', function () { grid.querySelectorAll('.day-button').forEach(item => { item.classList.remove('is-selected'); item.setAttribute('aria-pressed', 'false'); }); button.classList.add('is-selected'); button.setAttribute('aria-pressed', 'true'); input.value = day; }); grid.appendChild(button); } }
    month.addEventListener('change', render); render();
});
</script>
@endsection
