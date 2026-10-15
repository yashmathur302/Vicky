@extends('layouts.frontend.base')

@section('theme', 'light')
@section('body_class', 'app-body')

@section('layout_head')
    <script src="{{ asset('frontend/js/theme-init.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('frontend/css/layout.css') }}">
@endsection

@section('body')
    @include('layouts.frontend.partials.navbar')
    @include('frontend.partials.sidebar-left')

    <main class="tc-main">
        @yield('content')
    </main>

    @include('frontend.partials.chat-widget')
    
@endsection

@section('layout_scripts')
    <script src="{{ asset('frontend/js/layout.js') }}"></script>
    <script src="{{ asset('frontend/js/session.js') }}" defer
            data-keep-active-url="{{ route('keep.active') }}"
            data-logout-url="{{ route('logout') }}"
            data-login-url="{{ route('login') }}"
            data-remembered="{{ request()->hasCookie(Auth::guard('web')->getRecallerName()) ? '1' : '0' }}"
            data-idle-seconds="300"></script>
@endsection