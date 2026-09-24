@extends('layouts.dashbord')
@section('header_title', 'ویرایش ویزیت')
@section('content')
    @include('visits._form', ['patient' => $item->user, 'visit' => $item])
@endsection
