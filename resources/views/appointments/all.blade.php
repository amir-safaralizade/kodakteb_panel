@extends('layouts.dashbord')
@section('header_title', 'همه نوبت‌ها')

@section('content')
<link rel="stylesheet" href="{{ asset('matabgaleb/appointments.css') }}?v=6">

<div class="appointment-heading">
    <div>
        <h1 class="page-title">همه نوبت‌ها</h1>
        <p>لیست کامل نوبت‌های ثبت‌شده با امکان فیلتر بازه تاریخ شمسی</p>
    </div>
    <a class="btn btn-primary" href="{{ route('appointments.index') }}">ثبت نوبت جدید</a>
</div>

<form class="visit-date-filter" method="get" action="{{ route('appointments.all') }}">
    <div><label for="appointment-from">از تاریخ شمسی</label><input class="form-control" id="appointment-from" name="from" value="{{ request('from') }}" placeholder="۱۴۰۴/۰۱/۰۱" inputmode="numeric"></div>
    <div><label for="appointment-to">تا تاریخ شمسی</label><input class="form-control" id="appointment-to" name="to" value="{{ request('to') }}" placeholder="۱۴۰۴/۱۲/۲۹" inputmode="numeric"></div>
    <button class="btn btn-primary" type="submit">اعمال فیلتر</button>
    @if(request()->filled('from') || request()->filled('to'))<a class="btn btn-outline-primary" href="{{ route('appointments.all') }}">پاک‌کردن</a>@endif
</form>

<section class="appointment-list">
    <div class="appointment-list__head">
        <div><h2>فهرست نوبت‌ها</h2><span>{{ $items->total() }} نوبت</span></div>
    </div>
    <div class="table-scroll" tabindex="0" role="region" aria-label="فهرست همه نوبت‌ها">
        <table>
            <thead><tr><th>تاریخ</th><th>ساعت</th><th>مراجعه‌کننده</th><th>شماره همراه</th><th>پرونده</th><th>وضعیت نوبت</th><th>یادآوری پیامکی</th><th>توضیحات</th><th>عملیات</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td><span class="appointment-day-badge">{{ sprintf('%04d/%02d/%02d', $item->jalali_year, $item->jalali_month, $item->jalali_day) }}</span></td>
                    <td class="appointment-time">{{ substr($item->appointment_time, 0, 5) }}</td>
                    <td>{{ $item->patient_name ?: '—' }}@if($item->user?->Age())<small class="patient-age">{{ $item->user->Age() }}</small>@endif</td>
                    <td dir="ltr">{{ $item->phone }}</td>
                    <td>@if($item->user)<a class="patient-file-link" href="{{ route('user.show', $item->user_id) }}">پرونده {{ $item->user->caseNumber }}</a>@else<span class="standalone-badge">بدون پرونده</span>@endif</td>
                    <td>
                        @if($item->status === 'completed')
                            <span class="reminder-badge reminder-badge--sent">انجام‌شده</span>
                        @elseif($item->status === 'cancelled')
                            <span class="reminder-badge reminder-badge--failed">لغوشده</span>
                        @else
                            <span class="reminder-badge reminder-badge--pending">در انتظار</span>
                        @endif
                    </td>
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
                <tr><td colspan="9"><div class="empty-state"><span aria-hidden="true">▦</span><h3>نوبتی برای نمایش وجود ندارد</h3><p>در صورت نیاز بازه تاریخ را تغییر دهید یا نوبت جدید ثبت کنید.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $items->links('pagination::bootstrap-5') }}</div>
</section>
@endsection
