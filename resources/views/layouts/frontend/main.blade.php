<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'TeachYou'))</title>

    {{-- Saved theme ko render se pehle set karta hai (dark-mode flash se bachne ke liye) --}}
    <script>try{var t=localStorage.getItem('edu_theme');if(t==='light'||t==='dark'){document.documentElement.setAttribute('data-bs-theme',t)}}catch(e){}</script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v={{ filemtime(public_path('frontend/css/style.css')) }}">
    @stack('styles')
</head>
<body class="app-body"
      data-keep-active-url="{{ route('keep.active') }}"
      data-logout-url="{{ route('logout') }}"
      data-login-url="{{ route('login') }}"
      data-remembered="{{ request()->hasCookie(Auth::guard('web')->getRecallerName()) ? '1' : '0' }}"
      data-idle-seconds="300">

    @include('layouts.frontend.partials.navbar')
    @include('frontend.partials.sidebar-left')

    <main class="tc-main">
        @yield('content')
    </main>

    @include('frontend.partials.chat-widget')

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('frontend/js/main.js') }}?v={{ filemtime(public_path('frontend/js/main.js')) }}"></script>
    @stack('scripts')
</body>
</html>