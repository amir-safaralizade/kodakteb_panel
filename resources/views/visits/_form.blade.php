@php
    $editing = isset($visit);
    $insurances = $insurances ?? \App\Models\Insurance::all();
    $selectedInsurance = old('insurance_id', $editing ? $visit->insurance_id : ($patient->insurance_id ?? $insurances->first()?->id));
    $previousVisits = $patient->visits->when($editing, fn ($items) => $items->where('id', '!=', $visit->id))->sortByDesc('id')->take(10);
    $patientReminders = $patient->reminders->sortByDesc('id');
    $pendingPatientReminders = $patientReminders->where('status', 'pending');
@endphp
<div class="entry-heading">
    <div><span class="eyebrow">{{ $editing ? 'فضای تکمیل پزشک' : 'پذیرش بیمار' }}</span><h1>{{ $editing ? 'تکمیل ویزیت' : 'ثبت اولیه ویزیت' }}</h1><p>{{ $editing ? 'اطلاعات پزشکی ویزیت را بررسی و تکمیل کنید · '.$visit->CreateJ : 'منشی فقط اطلاعات پذیرش و پرداخت را ثبت می‌کند؛ اطلاعات پزشکی بعداً توسط پزشک تکمیل می‌شود.' }}</p></div>
    <a class="btn btn-outline-primary" href="{{ route('user.show', $patient->id) }}">مشاهده پرونده</a>
</div>
<section class="patient-context" aria-label="بیمار این ویزیت">
    <span class="patient-context-icon" aria-hidden="true">♡</span>
    <div><strong>{{ trim($patient->name.' '.$patient->lastName) ?: 'بیمار بدون نام' }}</strong><span>{{ $patient->sex ?: 'جنسیت ثبت نشده' }} @if($patient->birthday) · تولد {{ $patient->birthday }} @endif @if($patient->Age()) · سن {{ $patient->Age() }} @endif</span></div>
    <dl><div><dt>شماره پرونده</dt><dd>{{ $patient->caseNumber }}</dd></div><div><dt>کد ملی</dt><dd>{{ $patient->nationalCode ?: '—' }}</dd></div><div><dt>همراه</dt><dd dir="ltr">{{ $patient->phone ?: '—' }}</dd></div><div><dt>نام پدر</dt><dd>{{ $patient->fatherName ?: '—' }}</dd></div><div><dt>نام مادر</dt><dd>{{ trim($patient->motherName.' '.$patient->motherLastName) ?: '—' }}</dd></div><div><dt>بیمه پرونده</dt><dd>{{ $patient->insurance?->name ?? '—' }}</dd></div></dl>
</section>
@if($pendingPatientReminders->isNotEmpty())
<section class="patient-active-reminders" aria-labelledby="active-reminders-heading">
    <div class="active-reminders-heading"><span aria-hidden="true">⏰</span><div><h2 id="active-reminders-heading">یادآور فعال این بیمار</h2><p>توضیح پزشک در ویزیت قبلی را پیش از تکمیل ویزیت بررسی کنید.</p></div></div>
    <div class="active-reminders-list">
        @foreach($pendingPatientReminders as $reminder)
            <article><div class="active-reminder-date"><small>تاریخ مراجعه پیشنهادی</small><strong>{{ $reminder->jalali_date }}</strong></div><div class="active-reminder-content"><strong>ویزیت مجدد</strong><p>{{ $reminder->notes ?: 'توضیحی برای این یادآور ثبت نشده است.' }}</p>@if($reminder->visit)<small>ثبت‌شده در ویزیت {{ $reminder->visit->CreateJ }}</small>@endif</div><button class="btn btn-sm btn-outline-primary" type="submit" form="complete-reminder-{{ $reminder->id }}">انجام شد</button></article>
        @endforeach
    </div>
</section>
@endif
<section class="visit-history-panel" aria-labelledby="history-heading">
    <div class="history-heading"><div><h2 id="history-heading">سوابق ویزیت بیمار</h2><p>۱۰ ویزیت قبلی؛ بدون نیاز به ترک این صفحه</p></div><span>{{ $previousVisits->count() }} سابقه</span></div>
    <div class="history-cards">
        @forelse($previousVisits as $previous)
            <details class="history-card" @if($loop->first) open @endif>
                <summary><span class="history-card-arrow" aria-hidden="true">⌄</span><strong>{{ $previous->CreateJ }}</strong><span>{{ $previous->insurance?->name ?? 'بدون بیمه' }}</span><small>{{ $previous->is_clinically_completed ? 'تکمیل‌شده' : 'ثبت اولیه' }}</small></summary>
                <div class="history-card-body"><dl><div><dt>علت مراجعه</dt><dd>{{ $previous->elat ?: '—' }}</dd></div><div><dt>علائم و معاینه</dt><dd>{{ $previous->alaem ?: '—' }}</dd></div><div><dt>تشخیص</dt><dd>{{ $previous->tashkhis ?: '—' }}</dd></div><div><dt>برنامه درمان</dt><dd>{{ $previous->plan ?: '—' }}</dd></div>@if($previous->tozihat)<div class="history-card-wide"><dt>توضیحات</dt><dd>{{ $previous->tozihat }}</dd></div>@endif @foreach($previous->reminders as $historyReminder)<div class="history-card-wide history-reminder"><dt>یادآور ویزیت مجدد · {{ $historyReminder->jalali_date }}</dt><dd>{{ $historyReminder->notes ?: 'بدون توضیح' }} <span class="follow-up-status follow-up-status--{{ $historyReminder->status }}">{{ ['pending' => 'در انتظار', 'completed' => 'انجام‌شده', 'cancelled' => 'لغوشده'][$historyReminder->status] ?? $historyReminder->status }}</span></dd></div>@endforeach</dl><a class="history-edit-link" href="{{ route('visits.edit', $previous->id) }}?send_sms=false">ویرایش این ویزیت ←</a></div>
            </details>
        @empty
            <div class="history-empty"><span aria-hidden="true">♡</span><p>ویزیت قبلی برای این بیمار ثبت نشده است.</p></div>
        @endforelse
    </div>
</section>
<form class="clinical-form" data-clinical-form data-follow-up-days='@json($followUpMonthDays ?? [])' data-follow-up-weekdays='@json($followUpMonthWeekdays ?? [])' method="post" autocomplete="off" action="{{ $editing ? route('visits.update', ['visit' => $visit->id, 'send_sms' => request('send_sms', 'false')]) : route('visits.store', $patient->id) }}">
    @csrf
    @if($editing) @method('PUT') @endif
    @if($editing)
    <div class="workflow-banner workflow-banner--doctor"><span aria-hidden="true">✚</span><div><strong>مرحله تکمیل پزشک</strong><p>پذیرش بیمار انجام شده است. شرح حال، یافته‌های معاینه، تشخیص و برنامه درمان را ثبت کنید.</p></div></div>
    <section class="entry-section entry-section--clinical" aria-labelledby="clinical-heading">
        <div class="entry-section-heading"><span class="section-number">۱</span><div><h2 id="clinical-heading">شرح ویزیت</h2><p>از علت مراجعه تا برنامه درمان؛ به ترتیب معاینه</p></div></div>
        <div class="entry-grid">
            <x-clinic-field name="elat" label="علت مراجعه" type="textarea" :value="$visit->elat ?? ''" autofocus data-autogrow placeholder="شکایت اصلی بیمار…" />
            <x-clinic-field name="alaem" label="علائم بالینی و معاینه" type="textarea" :value="$visit->alaem ?? ''" data-autogrow placeholder="یافته‌های معاینه…" />
            <x-clinic-field name="tashkhis" label="تشخیص" type="textarea" :value="$visit->tashkhis ?? ''" data-autogrow />
            <x-clinic-field name="plan" label="برنامه تشخیصی و درمانی" type="textarea" :value="$visit->plan ?? ''" data-autogrow />
            <div class="entry-full-width"><x-clinic-field name="tozihat" label="توضیحات تکمیلی" type="textarea" :value="$visit->tozihat ?? ''" :rows="2" data-autogrow hint="در صورت نیاز" /></div>
        </div>
    </section>
    @else
    <div class="workflow-banner workflow-banner--reception"><span aria-hidden="true">✓</span><div><strong>ثبت سریع پذیرش</strong><p>بعد از ذخیره، این ویزیت در «ویزیت‌های امروز» و «آخرین ویزیت‌ها» برای تکمیل پزشک نمایش داده می‌شود.</p></div></div>
    @endif
    <section class="entry-section" aria-labelledby="payment-heading">
        <div class="entry-section-heading"><span class="section-number">{{ $editing ? '۲' : '۱' }}</span><div><h2 id="payment-heading">بیمه و دریافت</h2><p>اطلاعات مالی ثبت‌شده توسط پذیرش · واحد مبلغ: ریال</p></div></div>
        <div class="entry-grid three-columns">
            <x-clinic-field name="insurance_id" label="بیمه این ویزیت" type="select">
                @forelse($insurances as $insurance)
                    <option value="{{ $insurance->id }}" @selected((string) $selectedInsurance === (string) $insurance->id)>{{ $insurance->name }}</option>
                @empty
                    <option value="">بیمه‌ای تعریف نشده است</option>
                @endforelse
            </x-clinic-field>
            <x-clinic-field name="raveshdaryaft" label="روش دریافت" type="select">
                @foreach(['کارت' => 'کارت‌خوان', 'نقدی' => 'نقدی', 'ک ب ک' => 'کارت به کارت'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('raveshdaryaft', $visit->raveshdaryaft ?? 'کارت') === $value)>{{ $label }}</option>
                @endforeach
            </x-clinic-field>
            <div><x-clinic-field name="hazine" label="مبلغ دریافتی (ریال)" :value="$visit->hazine ?? ''" required inputmode="decimal" dir="ltr" data-money placeholder="مثلاً 1,500,000" /><output class="money-equivalent" for="field-hazine" data-money-output>معادل تومان پس از ورود مبلغ نمایش داده می‌شود.</output></div>
        </div>
        @unless($editing)
            <div class="entry-reception-note"><x-clinic-field name="tozihat" label="یادداشت اولیه برای پزشک" type="textarea" :value="''" :rows="2" data-autogrow hint="اختیاری؛ مانند دلیل کوتاه مراجعه یا توضیح منشی" placeholder="توضیح کوتاه پذیرش…" /></div>
        @endunless
    </section>
    @if($editing)
    <section class="entry-section follow-up-section" aria-labelledby="follow-up-heading">
        <div class="entry-section-heading"><span class="section-number">۳</span><div><h2 id="follow-up-heading">یادآور ویزیت مجدد</h2><p>در صورت نیاز، زمان پیشنهادی پزشک برای مراجعه بعدی را مشخص کنید.</p></div></div>
        @if($visit->reminders->isEmpty())
        <div class="entry-grid three-columns">
            <x-clinic-field name="follow_up_period" label="زمان مراجعه مجدد" type="select" data-follow-up-period>
                <option value="">نیازی به یادآور نیست</option>
                <option value="3" @selected(old('follow_up_period') === '3')>سه روز دیگر</option>
                <option value="7" @selected(old('follow_up_period') === '7')>یک هفته دیگر</option>
                <option value="14" @selected(old('follow_up_period') === '14')>دو هفته دیگر</option>
                <option value="30" @selected(old('follow_up_period') === '30')>یک ماه دیگر</option>
                <option value="custom" @selected(old('follow_up_period') === 'custom')>انتخاب تاریخ شمسی</option>
            </x-clinic-field>
            <x-clinic-field name="follow_up_notes" label="توضیح داخلی" :value="''" maxlength="1000" placeholder="مثلاً بررسی مجدد علائم…" />
        </div>
        <div class="follow-up-calendar" data-follow-up-custom @if(old('follow_up_period') !== 'custom') hidden @endif>
            <div class="appointment-field"><label for="follow-up-month">ماه مراجعه</label><select class="form-control" id="follow-up-month" name="follow_up_month" data-follow-up-month>@foreach(($followUpMonths ?? []) as $number => $name)<option value="{{ $number }}" @selected((int) old('follow_up_month', \Morilog\Jalali\Jalalian::now()->getMonth()) === $number)>{{ $name }}</option>@endforeach</select></div>
            <fieldset class="day-picker"><legend>انتخاب روز مراجعه</legend><div class="day-grid" data-follow-up-days-grid></div><input type="hidden" name="follow_up_day" value="{{ old('follow_up_day') }}" data-follow-up-day></fieldset>
        </div>
        @else
            <div class="follow-up-duplicate-note">برای هر ویزیت فقط یک یادآور قابل ثبت است. یادآور این ویزیت قبلاً ایجاد شده است.</div>
        @endif
        @if($visit->reminders->isNotEmpty())
            <div class="follow-up-list"><h3>یادآورهای ثبت‌شده</h3>@foreach($visit->reminders->sortByDesc('id') as $reminder)<div class="follow-up-item"><span class="follow-up-status follow-up-status--{{ $reminder->status }}">{{ ['pending' => 'در انتظار', 'completed' => 'انجام‌شده', 'cancelled' => 'لغوشده'][$reminder->status] ?? $reminder->status }}</span><strong>ویزیت مجدد · {{ $reminder->jalali_date }}</strong>@if($reminder->notes)<small>{{ $reminder->notes }}</small>@endif @if($reminder->status === 'pending')<span class="follow-up-actions"><button class="btn btn-sm btn-outline-primary" type="submit" form="complete-reminder-{{ $reminder->id }}">انجام شد</button><button class="reminder-cancel" type="submit" form="cancel-reminder-{{ $reminder->id }}" onclick="return confirm('این یادآور لغو شود؟')">لغو</button></span>@endif</div>@endforeach</div>
        @endif
    </section>
    @endif
    <div class="entry-actions">
        <div class="entry-save-state"><span data-save-state>آماده ورود اطلاعات</span><small><kbd>Ctrl</kbd> + <kbd>Enter</kbd> برای ذخیره</small></div>
        <div class="entry-action-buttons">
            @if($editing)
                <button class="btn btn-primary" type="submit" name="complete_visit" value="1" data-primary-submit>ذخیره و تکمیل ویزیت</button>
            @else
                <button class="btn btn-primary" type="submit" data-primary-submit>ثبت پذیرش ویزیت</button>
            @endif
            <a class="entry-cancel" href="{{ route('user.show', $patient->id) }}">انصراف</a>
        </div>
    </div>
</form>
@foreach($pendingPatientReminders as $reminder)
    <form id="complete-reminder-{{ $reminder->id }}" method="post" action="{{ route('visits.reminders.complete', $reminder) }}">@csrf @method('PATCH')</form>
    <form id="cancel-reminder-{{ $reminder->id }}" method="post" action="{{ route('visits.reminders.destroy', $reminder) }}">@csrf @method('DELETE')</form>
@endforeach
