@include('layouts.header')
@if(!request()->routeIs('login'))
    <a class="skip-link" href="#main-content">رفتن به محتوای اصلی</a>
    <div class="father" id="father">
        @include('layouts.sidebar')
        <div class="mainsection">
            <div class="cotainer-d">
                @include('layouts.navbar')
                <main class="main-sec" id="main-content" tabindex="-1">
                    @if(session('success'))
                        <div class="alert alert-success" role="status">{!! session('success') !!}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger" role="alert">{!! session('error') !!}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger" role="alert"><strong>لطفاً اطلاعات فرم را بررسی کنید.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                    @yield('content')
                </main>
                <footer class="workspace-footer"><span>کودک طب · سامانه مدیریت مطب</span><span>مراقبت بهتر، از نظم بیشتر شروع می‌شود.</span></footer>
            </div>
        </div>
    </div>
@else
    @yield('content')
@endif
@include('layouts.footer')
