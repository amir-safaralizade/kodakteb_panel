<header class="app-topbar">
    <button class="menu-toggle icon-button" type="button" aria-controls="app-sidebar" aria-expanded="false" aria-label="باز کردن منو">☰</button>
    <div class="breadcrumb-text">فضای کار مطب <span>/</span> <strong>@yield('header_title', 'نمای کلی')</strong></div>
    <div class="topbar-meta"><time data-today></time><span class="admin-avatar" aria-label="حساب مدیر">م</span></div>
</header>
<div class="workspace-toolbar">
    @if (!request()->routeIs('daysStatisics'))
        <form class="patient-search" method="post" action="{{ route('user.search') }}" role="search" data-suggestions-url="{{ route('user.suggestions') }}">
            @csrf
            <label class="visually-hidden" for="patient-search">جست‌وجوی بیمار</label>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
            <input id="patient-search" type="search" name="searchInput" placeholder="نام، کد ملی، موبایل یا شماره پرونده…" value="{{ request('searchInput') }}" required maxlength="80" autocomplete="off" aria-controls="patient-suggestions" aria-expanded="false" aria-describedby="search-status">
            <button class="btn btn-primary" type="submit">جست‌وجو</button>
            <div class="search-suggestions" id="patient-suggestions" hidden>
                <div class="suggestions-heading">پرونده‌های مرتبط <small>حداکثر ۵ نتیجه</small></div>
                <ul class="suggestions-list" aria-label="پرونده‌های پیشنهادی"></ul>
                <p class="suggestions-message" hidden></p>
            </div>
            <span id="search-status" class="visually-hidden" role="status" aria-live="polite"></span>
        </form>
    @else
        <form class="date-filter" method="get" action="{{ route('daysStatisics') }}">
            <div><label for="report-start">از تاریخ</label><input id="report-start" class="form-control" name="start" placeholder="۱۴۰۳/۰۱/۰۱" value="{{ request('start') }}"></div>
            <div><label for="report-end">تا تاریخ</label><input id="report-end" class="form-control" name="end" placeholder="۱۴۰۳/۱۲/۲۹" value="{{ request('end') }}"></div>
            <button class="btn btn-primary" type="submit">نمایش گزارش</button>
        </form>
    @endif
    <a class="btn btn-outline-primary new-patient" href="{{ route('user.create') }}"><span aria-hidden="true">＋</span> پرونده جدید</a>
</div>
