@extends('layouts.frontend.guest')

@section('title', 'Forgot Password')

@section('content')
<main class="auth-layout">
    <!-- Naya background lagayein -->
    <img class="auth-layout__bg" src="{{ asset('frontend/images/login_bg.jpg') }}" alt="Background">

    <!-- LEFT SECTION: INTRO -->
    <div class="auth-layout__intro">
        <a class="auth-layout__brand" href="#">
            <i class="fas fa-layer-group text-warning"></i>
            <span>{{ config('app.name', 'My Project') }}</span>
        </a>
        <p class="auth-layout__kicker">Account Recovery</p>
        <p class="auth-layout__headline">Lost your access? Let's get you back in.</p>
    </div>

    <!-- RIGHT SECTION: FORM GLASS CARD -->
    <section class="auth-glass">
        <div>
            <h1 class="auth-title">Forgot Password?</h1>
            <p class="auth-sub">No worries! Enter your email address below and we'll send you a password reset link.</p>

            <!-- Display Session Errors (like Expired Token) -->
            @if(session('error'))
                <div class="alert alert-danger text-center small fw-bold">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                </div>
            @endif

            <form id="frontendForgotForm" action="{{ route('frontend.forgot.password') }}" method="POST">
                @csrf
                
                <div class="auth-field">
                    <label class="auth-label" for="email">Email Address <span class="text-danger">*</span></label>
                    <input class="auth-input" id="email" name="email" type="email" autocomplete="email" required placeholder="you@example.com" autofocus>
                </div>

                <!-- AJAX Form Message Container -->
                <div class="form-message mt-2 mb-3" style="display: none;"></div>

                <button class="auth-submit" type="submit">
                    Send Reset Link
                    <i class="fas fa-paper-plane ms-2"></i>
                </button>
            </form>

            <p class="auth-foot mt-4">Remembered your password? <a class="auth-link" href="{{ route('login') }}">Back to Sign In</a></p>
        </div>
    </section>
</main>
@endsection