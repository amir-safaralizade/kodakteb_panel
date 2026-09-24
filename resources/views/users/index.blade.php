@extends('layouts.dashbord')
@section('header_title' , 'لیست پرونده ها')
@section('content')
    <h2 class="page-title">
        لیست پرونده ها
    </h2>
        <div class="table-scroll" tabindex="0" role="region" aria-label="جدول اطلاعات؛ برای مشاهده ستون‌ها به طرفین حرکت کنید"><table>
            <thead>
            <th>شماره پرونده</th>
            <th class="text-center"> نام و نام خانوادگی</th>
            <th>کدملی</th>
            <th>جنسیت</th>
            <th>تاریخ تولد</th>
            <th>سن</th>
            <th>مشاهده</th>
            <th> ویرایش </th>
            <th> حذف </th>

            </thead>
            @forelse($items as $item)
                <tr>
                    <td>{{$item->caseNumber}}</td>
                    <td class="text-center" >
                        <a href="{{route('user.show' , $item->id)}}" class="btn btn-sm btn-primary w-75">
                            {{$item->name.' '.$item->lastName}}
                        </a>
                    </td>
                    <td>{{$item->nationalCode}}</td>
                    <td>
                        @if($item->sex == 'دختر')
                            <div class="btn btn-warning btn-sm" style="background: #fcc2d7;border-color: #f783ac;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gender-female" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8 1a4 4 0 1 0 0 8 4 4 0 0 0 0-8M3 5a5 5 0 1 1 5.5 4.975V12h2a.5.5 0 0 1 0 1h-2v2.5a.5.5 0 0 1-1 0V13h-2a.5.5 0 0 1 0-1h2V9.975A5 5 0 0 1 3 5"/>
                                </svg>
                            </div>
                        @else
                            <div class="btn btn-warning btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gender-male" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M9.5 2a.5.5 0 0 1 0-1h5a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-1 0V2.707L9.871 6.836a5 5 0 1 1-.707-.707L13.293 2zM6 6a4 4 0 1 0 0 8 4 4 0 0 0 0-8"/>
                                </svg>
                            </div>
                        @endif
                    </td>
                    <td>{{$item->birthday}}</td>
                    <td>{{ $item->Age() ?: '—' }}</td>
                    <td>
                        <a href="{{route('user.show' , $item->id)}}" class="btn btn-sm btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                            <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                            <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                            </svg>
                        </a>
                    </td>
                    <td>
                        <a href="{{route('user.edit' , $item->id)}}" class="btn btn-sm btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                            </svg>
                        </a>
                    </td>
                    <td>
                        <form action="{{route('user.destroy' , $item->id)}}" method='POST'>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"
                            onclick="return confirm('در صورت حذف همه‌ی اطلاعات مربوطه حذف خواهد شد. آیا موافق هستید؟')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
<tr><td colspan="9"><div class="empty-state"><span aria-hidden="true">▤</span><h3>موردی برای نمایش وجود ندارد</h3><p>اطلاعات ثبت‌شده در این بخش نمایش داده می‌شود.</p></div></td></tr>
@endforelse
        </table></div>
        @if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator || $items instanceof \Illuminate\Pagination\Paginator)
            <div class="row">
                {{ $items->links('pagination::bootstrap-5') }}
            </div>
        @endif
    
@endsection
