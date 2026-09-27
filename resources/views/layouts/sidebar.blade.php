<aside class="app-sidebar" id="app-sidebar" aria-label="منوی اصلی">
    <a class="brand" href="{{ route('DashBord') }}"><img src="{{ asset('matabgaleb/assets/fav/logo.svg') }}" width="44" height="44" alt=""><span><strong>کودک طب</strong><small>سامانه مدیریت مطب</small></span></a>
    <button class="sidebar-close icon-button" type="button" aria-label="بستن منو">×</button>
    <nav class="sidebar-nav">
        <span class="nav-caption">فضای کار</span>
        <a class="nav-link {{ request()->routeIs('DashBord') ? 'is-active' : '' }}" href="{{ route('DashBord') }}"><span class="nav-symbol" aria-hidden="true">⌂</span> نمای کلی</a>
        <a class="nav-link {{ request()->routeIs('visits.todaysvisit') ? 'is-active' : '' }}" href="{{ route('visits.todaysvisit') }}"><span class="nav-symbol" aria-hidden="true">◷</span> ویزیت‌های امروز</a>
        <a class="nav-link {{ request()->routeIs('appointments.index', 'appointments.edit', 'appointments.patient') ? 'is-active' : '' }}" href="{{ route('appointments.index') }}"><span class="nav-symbol" aria-hidden="true">▦</span> نوبت‌دهی</a>
        <a class="nav-link {{ request()->routeIs('appointments.all') ? 'is-active' : '' }}" href="{{ route('appointments.all') }}"><span class="nav-symbol" aria-hidden="true">☷</span> همه نوبت‌ها</a>
        <a class="nav-link {{ request()->routeIs('reminders.index') ? 'is-active' : '' }}" href="{{ route('reminders.index') }}"><span class="nav-symbol" aria-hidden="true">◌</span> همه یادآورها</a>
        <span class="nav-caption">پرونده و درمان</span>
        <a class="nav-link {{ request()->routeIs('user.index', 'user.show', 'user.edit', 'user.search') ? 'is-active' : '' }}" href="{{ route('user.index') }}"><span class="nav-symbol" aria-hidden="true">▤</span> پرونده بیماران</a>
        <a class="nav-link {{ request()->routeIs('user.create') ? 'is-active' : '' }}" href="{{ route('user.create') }}"><span class="nav-symbol" aria-hidden="true">＋</span> تشکیل پرونده</a>
        <a class="nav-link {{ request()->routeIs('visits.index', 'visits.edit', 'visits.create') ? 'is-active' : '' }}" href="{{ route('visits.index') }}"><span class="nav-symbol" aria-hidden="true">♡</span> همه ویزیت‌ها</a>
        <a class="nav-link {{ request()->routeIs('daysStatisics') ? 'is-active' : '' }}" href="{{ route('daysStatisics') }}"><span class="nav-symbol" aria-hidden="true">▥</span> آمار و گزارش‌ها</a>
        @if(in_array(auth('admin')->user()->email, ['09143046229', '09054089235']))
            <span class="nav-caption">مدیریت سامانه</span>
            <a class="nav-link {{ request()->routeIs('admins.*', 'admin.*') ? 'is-active' : '' }}" href="{{ route('admins.list') }}"><span class="nav-symbol" aria-hidden="true">♧</span> مدیران</a>
            <a class="nav-link {{ request()->routeIs('messages.*') ? 'is-active' : '' }}" href="{{ route('messages.index') }}"><span class="nav-symbol" aria-hidden="true">✉</span> پیام‌ها</a>
        @endif
    </nav>
    <div class="sidebar-bottom"><div class="workspace-note"><span class="status-dot"></span> همراه شما در مراقبت بهتر<small>مدیریت ساده‌تر، تمرکز بیشتر</small></div><a class="logout-link" href="{{ route('logout') }}">خروج از حساب <span aria-hidden="true">↗</span></a></div>
</aside>
<button class="sidebar-overlay" type="button" aria-label="بستن منو" tabindex="-1" hidden></button>
