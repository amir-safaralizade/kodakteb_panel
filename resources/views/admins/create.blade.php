@extends('layouts.dashbord')
@section('header_title' , 'افزودن ادمین')

@section('content')
    <h2 class="page-title">
        افزودن ادمین جدید
    </h2>
    <form action="{{route('admins.store')}}" method="get">
        @csrf
        <div class="form-groups" >
            <div class="form-group flex-form-group">
                <input class="form-control" style="height: 100%" name="username" placeholder="نام کاربری" type="text" autofocus required>
                <input class="form-control" style="height: 100%" name="password" placeholder="رمز عبور" type="text" autofocus required>
            </div>
            <button type="submit" name="add-parvande" class="btn btn-success btn-block mt-3 success-btn">ثبت</button>
        </div>
    </form>
@endsection
