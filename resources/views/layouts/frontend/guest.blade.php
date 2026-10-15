@extends('layouts.frontend.base')

@section('body_class', 'auth-body')

@section('layout_head')
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">
@endsection

@section('body')
    @yield('content')
@endsection

@section('layout_scripts')
    <script src="{{ asset('frontend/js/auth.js') }}"></script>
@endsection