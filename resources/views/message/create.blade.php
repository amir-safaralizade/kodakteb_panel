@extends('layouts.dashbord')
@section('header_title' , 'ایجاد پیام')

@section('content')
    <h2 class="page-title">
        ایجاد پیام
    </h2>
    <form action="{{route('messages.store')}}" method="post">
        @csrf
        @method('POST')
        <div class="form-groups">
            <div class="form-group flex-form-group">
                <input class="form-control" style="height: 100%" name="message_key" placeholder="کلید یکتا" type="text" required>
            </div>

            <div class="form-group dt-area">
                <label for="tozihat" class="btn btn-primary px-5 w-75">پیام</label>
                <textarea class="form-control" cols="5" rows="5" name="message_text" style="height: 100%"></textarea>
            </div>

            <button type="submit" name="add-parvande" class="btn btn-success btn-block mt-3 success-btn"> ثبت پیام</button>
        </div>
    </form>
@endsection
