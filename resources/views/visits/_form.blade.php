@php
    $editing = isset($visit);
    $insurances = $insurances ?? \App\Models\Insurance::all();
    $selectedInsurance = old('insurance_id', $editing ? $visit->insurance_id : ($patient->insurance_id ?? $insurances->first()?->id));
    $previousVisits = $patient->visits->when($editing, fn ($items) => $items->where('id', '!=', $visit->id))->sortByDesc('id')->take(10);
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
<section class="visit-history-panel" aria-labelledby="history-heading">
    <div class="history-heading"><div><h2 id="history-heading">سوابق ویزیت بیمار</h2><p>۱۰ ویزیت قبلی؛ بدون نیاز به ترک این صفحه</p></div><span>{{ $previousVisits->count() }} سابقه</span></div>
    <div class="history-cards">
        @forelse($previousVisits as $previous)
            <article class="history-card">
                <header><strong>{{ $previous->CreateJ }}</strong><span>{{ $previous->insurance?->name ?? 'بدون بیمه' }}</span><a href="{{ route('visits.edit', $previous->id) }}?send_sms=false">ویرایش</a></header>
                <dl><div><dt>علت مراجعه</dt><dd>{{ $previous->elat ?: '—' }}</dd></div><div><dt>علائم و معاینه</dt><dd>{{ $previous->alaem ?: '—' }}</dd></div><div><dt>تشخیص</dt><dd>{{ $previous->tashkhis ?: '—' }}</dd></div><div><dt>برنامه درمان</dt><dd>{{ $previous->plan ?: '—' }}</dd></div>@if($previous->tozihat)<div class="history-card-wide"><dt>توضیحات</dt><dd>{{ $previous->tozihat }}</dd></div>@endif</dl>
            </article>
        @empty
            <div class="history-empty"><span aria-hidden="true">♡</span><p>ویزیت قبلی برای این بیمار ثبت نشده است.</p></div>
        @endforelse
    </div>
</section>
<form class="clinical-form" data-clinical-form method="post" autocomplete="off" action="{{ $editing ? route('visits.update', ['visit' => $visit->id, 'send_sms' => request('send_sms', 'false')]) : route('visits.store', $patient->id) }}">
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
