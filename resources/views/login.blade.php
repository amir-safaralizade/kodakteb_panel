@extends('layouts.dashbord')
@section('header_title', 'ورود به کودک طب')
@section('content')
<main class="login-page">
    <section class="login-story">
        <a class="brand" href="{{ route('login') }}"><img src="{{ asset('matabgaleb/assets/fav/logo.svg') }}" width="52" height="52" alt=""><span><strong>کودک طب</strong><small>سامانه مدیریت مطب</small></span></a>
        <div class="story-content"><span class="eyebrow">همراهِ روزهای پرمشغله شما</span><h1>زمان بیشتر برای <br>مراقبت از لبخندها.</h1><p>از اولین مراجعه تا ادامه درمان؛ پرونده‌ها، ویزیت‌ها و گزارش‌های مطب را در یک فضای منظم مدیریت کنید.</p><div class="care-illustration" aria-hidden="true"><div class="care-orbit"></div><div class="care-cross">+</div><div class="care-tag tag-one">♡ &nbsp; مراقبت با آرامش</div><div class="care-tag tag-two">✓ &nbsp; همه‌چیز در دسترس</div></div></div>
        <small class="story-footer">یک فضای یکپارچه برای مدیریت مطب شما</small>
    </section>
    <section class="login-form-section">
        <div class="login-card"><span class="eyebrow">خوش آمدید</span><h2>ورود به فضای کار</h2><p class="muted">برای دسترسی به پنل، اطلاعات حساب خود را وارد کنید.</p>
            <form action="{{ route('auth') }}" method="post" class="login-form">
                @csrf
                <div class="field"><label for="email">نام کاربری</label><input id="email" class="form-control @error('email') is-invalid @enderror" name="email" type="text" dir="ltr" autocomplete="username" value="{{ old('email') }}" required autofocus @error('email') aria-describedby="email-error" aria-invalid="true" @enderror>@error('email')<span id="email-error" class="text-danger field-error">{{ $message }}</span>@enderror</div>
                <div class="field"><label for="password">رمز عبور</label><div class="password-field"><input id="password" class="form-control" name="password" type="password" dir="ltr" autocomplete="current-password" required><button type="button" class="password-toggle" aria-controls="password" aria-pressed="false">نمایش</button></div>@error('password')<span class="text-danger field-error">{{ $message }}</span>@enderror</div>
                <button class="btn btn-primary login-submit" type="submit">ورود به پنل <span aria-hidden="true">←</span></button>
            </form>
            <p class="login-note">ویژه پزشک و همکاران مطب</p>
        </div>
        <small class="login-copyright">کودک طب · مدیریت منظم، مراقبت بهتر</small>
    </section>
</main>
@endsection
