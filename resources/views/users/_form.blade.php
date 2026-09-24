@php
    $editing = isset($item);
    $insurances = $insurances ?? \App\Models\Insurance::orderByDesc('id')->get();
@endphp
<div class="entry-heading">
    <div><span class="eyebrow">پذیرش و اطلاعات بیمار</span><h1>{{ $editing ? 'ویرایش پرونده' : 'تشکیل پرونده جدید' }}</h1><p>اطلاعات اصلی در ابتدا؛ مشخصات خانواده در بخش بعد.</p></div>
    <div class="record-summary"><span class="record-number">شماره پرونده <b>{{ $editing ? $item->caseNumber : $lastCaseNumber }}</b></span>@if($editing && $item->Age())<span class="record-age">سن بیمار <b>{{ $item->Age() }}</b></span>@endif</div>
</div>
<form class="clinical-form" data-clinical-form method="post" action="{{ $editing ? route('user.update', $item->id) : route('user.store') }}" autocomplete="off">
    @csrf
    @if($editing) @method('PUT') @endif
    <input type="hidden" name="caseNumber" value="{{ $editing ? $item->caseNumber : $lastCaseNumber }}">
    @error('caseNumber')<div class="alert alert-danger" role="alert">{{ $message }}</div>@enderror
    <section class="entry-section" aria-labelledby="identity-heading">
        <div class="entry-section-heading"><span class="section-number">۱</span><div><h2 id="identity-heading">مشخصات اصلی بیمار</h2><p>برای شناسایی بیمار و ارتباط با خانواده</p></div></div>
        <div class="entry-grid three-columns">
            <x-clinic-field name="name" label="نام بیمار" :value="$item->name ?? ''" :required="$editing" autofocus maxlength="255" />
            <x-clinic-field name="lastname" label="نام خانوادگی" :value="$item->lastName ?? ''" maxlength="255" />
            <x-clinic-field name="phone" label="شماره همراه" :value="$item->phone ?? ''" :required="$editing" type="tel" inputmode="tel" dir="ltr" data-normalize-digits placeholder="09…" hint="شماره در دسترس والدین" />
            <x-clinic-field name="nationalCode" label="کد ملی" :value="$item->nationalCode ?? ''" inputmode="numeric" dir="ltr" data-normalize-digits />
            <x-clinic-field name="birthday" label="تاریخ تولد (شمسی)" :value="$item->birthday ?? ''" dir="ltr" data-jalali-date placeholder="1400/01/25" hint="سال / ماه / روز؛ ورود ۸ رقم پشت‌سرهم هم ممکن است." />
            <x-clinic-field name="sex" label="جنسیت" type="select">
                <option value="">انتخاب کنید</option>
                <option value="دختر" @selected(old('sex', $item->sex ?? '') === 'دختر')>دختر</option>
                <option value="پسر" @selected(old('sex', $item->sex ?? '') === 'پسر')>پسر</option>
            </x-clinic-field>
        </div>
    </section>
    <section class="entry-section" aria-labelledby="family-heading">
        <div class="entry-section-heading"><span class="section-number">۲</span><div><h2 id="family-heading">خانواده و پوشش بیمه</h2><p>اطلاعات تکمیلی پرونده</p></div></div>
        <div class="entry-grid three-columns">
            <x-clinic-field name="fatherName" label="نام پدر" :value="$item->fatherName ?? ''" maxlength="255" />
            <x-clinic-field name="motherName" label="نام مادر" :value="$item->motherName ?? ''" maxlength="255" />
            <x-clinic-field name="motherLastName" label="نام خانوادگی مادر" :value="$item->motherLastName ?? ''" maxlength="255" />
            <x-clinic-field name="insurance" label="بیمه بیمار" type="select">
                @forelse($insurances as $insurance)
                    <option value="{{ $insurance->id }}" @selected((string) old('insurance', $item->insurance_id ?? $insurances->first()?->id) === (string) $insurance->id)>{{ $insurance->name }}</option>
                @empty
                    <option value="">بیمه‌ای تعریف نشده است</option>
                @endforelse
            </x-clinic-field>
        </div>
    </section>
    <div class="entry-actions">
        <div class="entry-save-state"><span data-save-state>آماده ورود اطلاعات</span><small><kbd>Ctrl</kbd> + <kbd>Enter</kbd> برای ذخیره</small></div>
        <div class="entry-action-buttons">
            <button class="btn btn-primary" type="submit" data-primary-submit>{{ $editing ? 'ذخیره تغییرات پرونده' : 'ثبت پرونده' }}</button>
            @unless($editing)<button class="btn btn-outline-primary" type="submit" name="next" value="visit">ثبت و شروع ویزیت</button>@endunless
            <a class="entry-cancel" href="{{ $editing ? route('user.show', $item->id) : route('user.index') }}">انصراف</a>
        </div>
    </div>
</form>
