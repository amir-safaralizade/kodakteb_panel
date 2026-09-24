@extends('layouts.dashbord')

@if (isset($is_todaysVisit) && $is_todaysVisit == true)
    @section('header_title', 'ویزیت های امروز')
@else
    @section('header_title', 'لیست ویزیت ها')
@endif

@section('content')
    <h2 class="page-title">
        @if (request()->is('dashbord/info/visits/todaysvisits'))
            ویزیت های امروز
        @else
            لیست ویزیت ها
        @endif
    </h2>
    @unless(isset($is_todaysVisit) && $is_todaysVisit)
        <form class="visit-date-filter" method="get" action="{{ route('visits.index') }}">
            <div><label for="visit-from">از تاریخ شمسی</label><input class="form-control" id="visit-from" name="from" value="{{ request('from') }}" placeholder="۱۴۰۴/۰۱/۰۱" inputmode="numeric"></div>
            <div><label for="visit-to">تا تاریخ شمسی</label><input class="form-control" id="visit-to" name="to" value="{{ request('to') }}" placeholder="۱۴۰۴/۱۲/۲۹" inputmode="numeric"></div>
            <button class="btn btn-primary" type="submit">اعمال فیلتر</button>
            @if(request()->filled('from') || request()->filled('to'))<a class="btn btn-outline-primary" href="{{ route('visits.index') }}">پاک‌کردن</a>@endif
        </form>
    @endunless
    <div class="table-scroll" tabindex="0" role="region" aria-label="جدول اطلاعات؛ برای مشاهده ستون‌ها به طرفین حرکت کنید"><table>
        <thead>
            <th>شماره پرونده</th>
            <th class="text-center"> نام و نام خانوادگی</th>
            <th>کدملی</th>
            <th>بیمه</th>
            <th>جنسیت</th>
            <th>تاریخ تولد</th>
            <th>هزینه</th>
            <th> دریافت</th>
            <th>عملیات</th>
            <th>تاریخ</th>
        </thead>
        @php($lastShift = null)
        @forelse ($items as $item)
            @php($itemShift = \Carbon\Carbon::parse($item->created_at, 'Asia/Tehran')->format('H:i') < '16:00' ? 'صبح · تا ساعت ۱۶' : 'عصر · از ساعت ۱۶ به بعد')
            @if(isset($is_todaysVisit) && $is_todaysVisit && $lastShift !== $itemShift)
                <tr class="visit-shift-row"><td colspan="10"><strong>{{ $itemShift }}</strong></td></tr>
                @php($lastShift = $itemShift)
            @endif
            <tr>
                <td>
                    @if ($item->user)
                        {{ $item->user->caseNumber }}
                    @endif
                </td>
                <td class="text-center">
                    @if($item->user)<a href="{{ route('user.show', $item->user->id) }}" class="btn btn-sm btn-primary w-75">{{ $item->user->name . ' ' . $item->user->lastName }}</a>@else — @endif
                    <small class="visit-completion {{ $item->is_clinically_completed ? 'is-complete' : 'is-pending' }}">{{ $item->is_clinically_completed ? 'تکمیل‌شده' : 'در انتظار تکمیل پزشک' }}</small>
                </td>
                <td>{{ $item->user?->nationalCode ?? '—' }}</td>
                <td>{{ $item->insurance?->name ?? '—' }}</td>
                <td>
                    @if ($item->user?->sex == 'دختر')
                        <div class="btn btn-warning btn-sm" style="background: #fcc2d7;border-color: #f783ac;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-gender-female" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M8 1a4 4 0 1 0 0 8 4 4 0 0 0 0-8M3 5a5 5 0 1 1 5.5 4.975V12h2a.5.5 0 0 1 0 1h-2v2.5a.5.5 0 0 1-1 0V13h-2a.5.5 0 0 1 0-1h2V9.975A5 5 0 0 1 3 5" />
                            </svg>
                        </div>
                    @else
                        <div class="btn btn-warning btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-gender-male" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M9.5 2a.5.5 0 0 1 0-1h5a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-1 0V2.707L9.871 6.836a5 5 0 1 1-.707-.707L13.293 2zM6 6a4 4 0 1 0 0 8 4 4 0 0 0 0-8" />
                            </svg>
                        </div>
                    @endif
                </td>
                <td>{{ $item->user?->birthday ?? '—' }}@if($item->user?->Age())<small class="patient-age">سن: {{ $item->user->Age() }}</small>@endif</td>
                <td>
                    <span style="letter-spacing:1px;font-weight:bold">
                        @if (is_numeric($item->hazine))
                            {{ number_format($item->hazine / 10) }}
                        @endif
                    </span>
                    تومان
                </td>
                <td>{{ $item->raveshdaryaft }}</td>
                <td class="d-flex flex-column gap-1">
                    <div>
                        <?php
                        if (isset($is_todaysVisit) && $is_todaysVisit == true) {
                            $route = route('visits.edit', $item->id) . '?send_sms=true';
                        } else {
                            $route = route('visits.edit', $item->id) . '?send_sms=false';
                        }
                        ?>
                        <a href="{{ $route }}" class="btn btn-sm {{ $item->is_clinically_completed ? 'btn-outline-primary' : 'btn-primary' }}">{{ $item->is_clinically_completed ? 'ویرایش ویزیت' : 'تکمیل ویزیت' }}</a>
                    </div>

                    <div>
                        <form method='POST' action="{{ route('visits.destroy', $item->id) }}">
                            @csrf
                            @method('DELETE')
                            <button
                                onclick="return confirm('در صورت حذف همه‌ی اطلاعات مربوطه حذف خواهد شد. آیا موافق هستید؟')"
                                type="submit" class="btn btn-danger btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-trash" viewBox="0 0 16 16">
                                    <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                    <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                                </svg>
                            </button>
                        </form>
                    </div>

                </td>
                <td>
                    {{ $item->CreateJ }}
                </td>
            </tr>
        @empty
<tr><td colspan="10"><div class="empty-state"><span aria-hidden="true">▤</span><h3>موردی برای نمایش وجود ندارد</h3><p>اطلاعات ثبت‌شده در این بخش نمایش داده می‌شود.</p></div></td></tr>
@endforelse
        @if (isset($is_todaysVisit) && $is_todaysVisit == true)
            <tr style="background: #e7ffe4;">
                <td>#</td>
                <td>مجموع درآمد امروز:</td>
                <td>{{ number_format($toal_income / 10) }} تومان</td>
                <td>تعداد : </td>
                <td>{{ $toal_count }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endif
    </table></div>
    @if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator || $items instanceof \Illuminate\Pagination\Paginator)
        <div class="row">
            {{ $items->links('pagination::bootstrap-5') }}
        </div>
    @endif
@endsection
