@extends('layouts.dashbord')
@section('header_title', 'نمای کلی')
@section('content')
<section class="welcome-banner">
    <div><span class="eyebrow">فضای کار کودک طب</span><h1>روز خوبی برای مراقبت بهتر است.</h1><p>نگاهی به فعالیت مطب؛ همه آنچه برای شروع روز نیاز دارید.</p><a class="btn btn-primary" href="{{ route('visits.todaysvisit') }}">مشاهده ویزیت‌های امروز <span aria-hidden="true">←</span></a></div>
    <div class="welcome-art" aria-hidden="true"><span class="art-ring"></span><span class="art-cross">+</span><span class="art-heart">♡</span></div>
</section>
<section class="stats-grid" aria-label="آمار مطب">
    <article class="stat-card"><span class="stat-icon mint" aria-hidden="true">▤</span><div><p>پرونده‌های ثبت‌شده</p><strong>{{ number_format($patientCount) }}</strong><small>پرونده بیمار</small></div></article>
    <article class="stat-card"><span class="stat-icon blue" aria-hidden="true">♡</span><div><p>ویزیت‌های امروز</p><strong>{{ number_format($todayCount) }}</strong><small>ویزیت ثبت‌شده</small></div></article>
    <article class="stat-card"><span class="stat-icon amber" aria-hidden="true">↗</span><div><p>درآمد امروز</p><strong>{{ number_format($todayIncome / 10) }}</strong><small>تومان</small></div></article>
</section>
<section class="surface dashboard-appointments">
    <div class="section-heading"><div><h2>نوبت‌های امروز</h2><p>{{ $todayAppointments->count() }} نوبت برنامه‌ریزی‌شده برای امروز</p></div><a href="{{ route('appointments.index') }}">مدیریت نوبت‌ها ←</a></div>
    <div class="today-shifts">
        @foreach(['morning' => ['صبح', 'تا ساعت ۱۶', $todayAppointments->filter(fn($item) => substr($item->appointment_time, 0, 5) < '16:00')], 'evening' => ['عصر', 'از ساعت ۱۶ به بعد', $todayAppointments->filter(fn($item) => substr($item->appointment_time, 0, 5) >= '16:00')]] as $shift => [$title, $caption, $shiftAppointments])
        <section class="today-shift today-shift--{{ $shift }}"><header><div><h3>{{ $title }}</h3><small>{{ $caption }}</small></div><span>{{ $shiftAppointments->count() }} نوبت</span></header><div class="table-scroll"><table><thead><tr><th>ساعت</th><th>بیمار</th><th>پرونده</th><th>عملیات</th></tr></thead><tbody>
            @forelse($shiftAppointments as $appointment)<tr><td><strong dir="ltr">{{ substr($appointment->appointment_time, 0, 5) }}</strong></td><td>{{ $appointment->patient_name }}@if($appointment->user?->Age())<small class="patient-age">{{ $appointment->user->Age() }}</small>@endif</td><td>{{ $appointment->user?->caseNumber ?? '—' }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('appointments.edit', $appointment) }}">ویرایش</a></td></tr>@empty<tr><td colspan="4" class="shift-empty">موردی ثبت نشده است.</td></tr>@endforelse
        </tbody></table></div></section>
        @endforeach
    </div>
</section>
<section class="surface dashboard-today-visits">
    <div class="section-heading"><div><h2>ویزیت‌های امروز</h2><p>{{ $todayVisits->count() }} پذیرش برای تکمیل پزشک</p></div><a href="{{ route('visits.todaysvisit') }}">مشاهده همه ←</a></div>
    <div class="today-shifts">
        @foreach(['morning' => ['صبح', 'تا ساعت ۱۶', $todayVisits->filter(fn($item) => \Carbon\Carbon::parse($item->created_at)->format('H:i') < '16:00')], 'evening' => ['عصر', 'از ساعت ۱۶ به بعد', $todayVisits->filter(fn($item) => \Carbon\Carbon::parse($item->created_at)->format('H:i') >= '16:00')]] as $shift => [$title, $caption, $shiftVisits])
        <section class="today-shift today-shift--{{ $shift }}"><header><div><h3>{{ $title }}</h3><small>{{ $caption }}</small></div><span>{{ $shiftVisits->count() }} ویزیت</span></header><div class="table-scroll"><table><thead><tr><th>زمان پذیرش</th><th>بیمار</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
            @forelse($shiftVisits as $visit)<tr><td dir="ltr">{{ \Carbon\Carbon::parse($visit->created_at)->format('H:i') }}</td><td>{{ trim(($visit->user?->name ?? '').' '.($visit->user?->lastName ?? '')) ?: 'پرونده در دسترس نیست' }}@if($visit->user?->Age())<small class="patient-age">{{ $visit->user->Age() }}</small>@endif</td><td><small class="visit-completion {{ $visit->is_clinically_completed ? 'is-complete' : 'is-pending' }}">{{ $visit->is_clinically_completed ? 'تکمیل‌شده' : 'در انتظار پزشک' }}</small></td><td><a class="btn btn-sm {{ $visit->is_clinically_completed ? 'btn-outline-primary' : 'btn-primary' }}" href="{{ route('visits.edit', $visit) }}?send_sms=true">{{ $visit->is_clinically_completed ? 'ویرایش' : 'تکمیل ویزیت' }}</a></td></tr>@empty<tr><td colspan="4" class="shift-empty">موردی ثبت نشده است.</td></tr>@endforelse
        </tbody></table></div></section>
        @endforeach
    </div>
</section>
<section class="surface dashboard-reminders">
    <div class="section-heading"><div><h2>پیگیری ویزیت مجدد</h2><p>یادآورهای سررسیدشده و هفت روز آینده</p></div><span class="section-count">{{ $upcomingReminders->count() }} مورد</span></div>
    <div class="reminder-dashboard-list">
        @forelse($upcomingReminders as $reminder)
            <article class="reminder-dashboard-item"><span class="reminder-date">{{ $reminder->jalali_date }}</span><div><strong>{{ trim(($reminder->user?->name ?? '').' '.($reminder->user?->lastName ?? '')) ?: 'بیمار بدون نام' }}</strong><small>{{ $reminder->notes ?: 'ویزیت مجدد طبق پیشنهاد پزشک' }}</small></div><a href="{{ $reminder->visit ? route('visits.edit', $reminder->visit_id) : route('user.show', $reminder->user_id) }}">مشاهده</a><form method="post" action="{{ route('visits.reminders.complete', $reminder) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-primary" type="submit">انجام شد</button></form></article>
        @empty
            <div class="shift-empty">یادآور نزدیک یا سررسیدشده‌ای وجود ندارد.</div>
        @endforelse
    </div>
</section>
<div class="dashboard-grid">
    <section class="surface recent-visits">
        <div class="section-heading"><div><h2>آخرین ویزیت‌ها</h2><p>تازه‌ترین مراجعات ثبت‌شده در مطب</p></div><a href="{{ route('visits.index') }}">مشاهده همه ←</a></div>
        <div class="table-scroll" tabindex="0" role="region" aria-label="آخرین ویزیت‌ها">
            <table><thead><tr><th>بیمار</th><th>شماره پرونده</th><th>بیمه</th><th>تاریخ مراجعه</th><th>جزئیات</th></tr></thead><tbody>
                @forelse($recentVisits as $visit)
                    <tr><td><span class="patient-name">{{ $visit->user ? $visit->user->name . ' ' . $visit->user->lastName : 'پرونده در دسترس نیست' }}</span>@if($visit->user?->Age())<small class="patient-age">{{ $visit->user->Age() }}</small>@endif<small class="visit-completion {{ $visit->is_clinically_completed ? 'is-complete' : 'is-pending' }}">{{ $visit->is_clinically_completed ? 'تکمیل‌شده' : 'در انتظار تکمیل پزشک' }}</small></td><td>{{ $visit->user?->caseNumber ?? '—' }}</td><td><span class="soft-badge">{{ $visit->insurance?->name ?? '—' }}</span></td><td>{{ $visit->CreateJ }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('visits.edit', $visit->id) }}?send_sms={{ \Carbon\Carbon::parse($visit->created_at, 'Asia/Tehran')->isToday() ? 'true' : 'false' }}">{{ $visit->is_clinically_completed ? 'ویرایش' : 'تکمیل ویزیت' }}</a></td></tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state"><span aria-hidden="true">♡</span><h3>هنوز ویزیتی ثبت نشده</h3><p>با انتخاب پرونده بیمار، اولین ویزیت را ثبت کنید.</p><a class="btn btn-outline-primary" href="{{ route('user.index') }}">مشاهده پرونده‌ها</a></div></td></tr>
                @endforelse
            </tbody></table>
        </div>
    </section>
    <section class="surface quick-actions"><div class="section-heading"><div><h2>دسترسی سریع</h2><p>کارهای روزمره، یک قدم نزدیک‌تر</p></div></div>
        <a class="quick-action" href="{{ route('user.create') }}"><span class="quick-icon">＋</span><span><strong>تشکیل پرونده</strong><small>ثبت اطلاعات بیمار جدید</small></span><span aria-hidden="true">←</span></a>
        <a class="quick-action" href="{{ route('user.index') }}"><span class="quick-icon">▤</span><span><strong>پرونده بیماران</strong><small>سوابق درمان و ثبت ویزیت</small></span><span aria-hidden="true">←</span></a>
        <a class="quick-action" href="{{ route('appointments.index') }}"><span class="quick-icon">▦</span><span><strong>نوبت‌دهی</strong><small>ثبت و مدیریت نوبت‌ها</small></span><span aria-hidden="true">←</span></a>
        <a class="quick-action" href="{{ route('daysStatisics') }}"><span class="quick-icon">▥</span><span><strong>گزارش عملکرد</strong><small>بررسی مراجعات و درآمد</small></span><span aria-hidden="true">←</span></a>
        <div class="care-note"><span aria-hidden="true">✧</span><p>نظم در اطلاعات،<br><strong>آرامش در مراقبت.</strong></p></div>
    </section>
</div>
@endsection
