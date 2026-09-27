@extends('layouts.dashbord')
@section('header_title', 'همه یادآورها')

@section('content')
<link rel="stylesheet" href="{{ asset('matabgaleb/appointments.css') }}?v=9">

<div class="appointment-heading">
    <div>
        <h1 class="page-title">همه یادآورها</h1>
        <p>لیست کامل یادآورهای ثبت‌شده برای پیگیری ویزیت مجدد و موارد بعدی</p>
    </div>
</div>

<form class="visit-date-filter" method="get" action="{{ route('reminders.index') }}">
    <div><label for="reminder-from">از تاریخ شمسی</label><input class="form-control" id="reminder-from" name="from" value="{{ request('from') }}" placeholder="۱۴۰۴/۰۱/۰۱" inputmode="numeric"></div>
    <div><label for="reminder-to">تا تاریخ شمسی</label><input class="form-control" id="reminder-to" name="to" value="{{ request('to') }}" placeholder="۱۴۰۴/۱۲/۲۹" inputmode="numeric"></div>
    <button class="btn btn-primary" type="submit">اعمال فیلتر</button>
    @if(request()->filled('from') || request()->filled('to'))<a class="btn btn-outline-primary" href="{{ route('reminders.index') }}">پاک‌کردن</a>@endif
</form>

<section class="appointment-list">
    <div class="appointment-list__head">
        <div><h2>فهرست یادآورها</h2><span>{{ $items->total() }} یادآور</span></div>
    </div>
    <div class="table-scroll" tabindex="0" role="region" aria-label="فهرست همه یادآورها">
        <table>
            <thead><tr><th>تاریخ مراجعه</th><th>نوع</th><th>بیمار</th><th>پرونده</th><th>ویزیت مرتبط</th><th>وضعیت</th><th>پیامک</th><th>توضیحات</th><th>عملیات</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td><span class="appointment-day-badge">{{ $item->jalali_date }}</span></td>
                    <td>{{ $item->type === 'revisit' ? 'ویزیت مجدد' : $item->type }}</td>
                    <td>{{ trim(($item->user?->name ?? '').' '.($item->user?->lastName ?? '')) ?: '—' }}@if($item->user?->Age())<small class="patient-age">{{ $item->user->Age() }}</small>@endif</td>
                    <td>@if($item->user)<a class="patient-file-link" href="{{ route('user.show', $item->user_id) }}">پرونده {{ $item->user->caseNumber }}</a>@else — @endif</td>
                    <td>@if($item->visit)<a class="patient-file-link" href="{{ route('visits.edit', $item->visit_id) }}">ویرایش ویزیت #{{ $item->visit_id }}</a>@else — @endif</td>
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
                        @if($item->sms_sent_at)
                            <span class="reminder-badge reminder-badge--sent" title="{{ $item->sms_sent_at->format('Y-m-d H:i') }}">✓ ارسال شده</span>
                        @elseif($item->status !== 'pending')
                            <span class="reminder-badge reminder-badge--muted">ارسال نمی‌شود</span>
                        @elseif($item->sms_attempts > 0 && $item->sms_error)
                            <span class="reminder-badge reminder-badge--failed" title="{{ $item->sms_error }}">! ناموفق</span>
                        @else
                            <span class="reminder-badge reminder-badge--pending">◷ در انتظار</span>
                        @endif
                    </td>
                    <td class="appointment-notes">{{ $item->notes ?: '—' }}</td>
                    <td>
                        <div class="appointment-actions">
                            @if($item->status === 'pending')
                                <form method="post" action="{{ route('visits.reminders.complete', $item) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-success" type="submit">انجام شد</button></form>
                                <form method="post" action="{{ route('visits.reminders.destroy', $item) }}">@csrf @method('DELETE')<button class="appointment-delete" type="submit" onclick="return confirm('این یادآور لغو شود؟')" aria-label="لغو یادآور">×</button></form>
                            @else
                                —
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9"><div class="empty-state"><span aria-hidden="true">◌</span><h3>یادآوری برای نمایش وجود ندارد</h3><p>در صورت نیاز بازه تاریخ را تغییر دهید.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $items->links('pagination::bootstrap-5') }}</div>
</section>
@endsection
