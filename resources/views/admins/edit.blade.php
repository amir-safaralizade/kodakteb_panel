@extends('layouts.dashbord')
@section('header_title' , 'ویرایش ادمین')
@section('content')
    <h2 class="page-title">
        ویرایش ادمین
    </h2>
    <form action="{{route('admins.update' , $admin->id)}}" method="get">
        @csrf
        <div class="form-groups" >
            <div class="form-group flex-form-group">
                <input class="form-control" style="height: 100%" name="username" placeholder="نام کاربری" type="text" autofocus required value="{{$admin->email}}">
                <input class="form-control" style="height: 100%" name="password" placeholder="رمز عبور" type="text" autofocus >
            </div>
            <button type="submit" name="add-parvande" class="btn btn-success btn-block mt-3 success-btn"> ثبت  </button>
        </div>
    </form>
@endsection
