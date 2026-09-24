@extends('layouts.dashbord')
@section('header_title', 'ثبت ویزیت جدید')
@section('content')
    @include('visits._form', ['patient' => $item])
@endsection
