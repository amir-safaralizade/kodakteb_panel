@extends('layouts.dashbord')
@section('header_title' , 'مشاهده پرونده')

@section('content')
    <div class="profile-box">

        @include('components.user-info' , ['item' , $item])

        <div id="accordion-wraper" style="width: 100%">
            @forelse($item->visits as $visit)
                <button class="accordion"> ویزیت ... {{$visit->CreateJ}} </button>
                <div class="panel">
                    <div class="vis-box">
                        <p><strong>علت مراجعه</strong>: {{$visit->elat}}</p>
                        <p><strong>علائم</strong> : {{$visit->alaem}}</p>
                        <p><strong>تشخیص :</strong> {{$visit->tashkhis}}  </p>
                        <p><strong>پلن تشخیصی درمانی : </strong>{{$visit->plan}}</p>
                        <p><strong>بیمه : </strong>{{$visit->insurance->name}}</p>
                        <p><strong>دریافتی :</strong> {{$visit->hazine}}</p>
                        <a class="ed-v" href="{{ route('visits.edit', $visit->id) }}?send_sms=false">ویرایش ویزیت</a>
                    </div>
                </div>
            @empty
                <div class="empty-state"><span aria-hidden="true">♡</span><h3>هنوز ویزیتی برای این بیمار ثبت نشده</h3><p>از دکمه زیر برای ثبت اولین مراجعه استفاده کنید.</p></div>
            @endforelse
        </div>

        <div class="sabt-visit-button">
            <a href="{{route('visits.create', $item->id)}}">ثبت ویزیت جدید</a>
        </div>

    </div>
@endsection
