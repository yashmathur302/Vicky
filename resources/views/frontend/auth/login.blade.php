@extends('layouts.frontend.guest')

@section('title', 'Sign In')

@section('content')
<main class="auth-layout">
    <img class="auth-layout__bg" src="{{ asset('frontend/images/login_bg.jpg') }}" alt="Background">

    <!-- LEFT SECTION: INTRO -->
    <div class="auth-layout__intro">
        <a class="auth-layout__brand" href="{{ route('login') }}">
            <i class="fas fa-layer-group text-warning"></i>
            <span>{{ config('app.name', 'My Project') }}</span>
        </a>
        <p class="auth-layout__kicker">Welcome to our platform</p>
        <p class="auth-layout__headline">Every tool your team has ever loved, in one place.</p>
    </div>

    <!-- RIGHT SECTION: LOGIN FORM GLASS CARD -->
    <section class="auth-glass">
        <div>
            <h1 class="auth-title">Sign in</h1>
            <p class="auth-sub">Good to see you again.</p>

            <!-- Server Session Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show small" role="alert">
                    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                    <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form id="frontendLoginForm" action="{{ route('login.submit') }}" method="POST">
                @csrf
                
                <div class="auth-field">
                    <label class="auth-label" for="login_id">Email or Username <span class="text-danger">*</span></label>
                    <input class="auth-input" id="login_id" name="login_id" type="text" autocomplete="username" required placeholder="you@example.com">
                </div>

                <div class="auth-field">
                    <label class="auth-label" for="password">Password <span class="text-danger">*</span></label>
                    <div class="auth-pw-wrapper input-group">
                        <input class="auth-input" id="password" name="password" type="password" autocomplete="current-password" required placeholder="Enter your password" style="border-radius: 14px">
                        <button class="auth-eye password-toggle" type="button" aria-label="Toggle Password Visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="auth-row">
                    <label class="auth-check">
                        <input type="checkbox" name="remember"> <span>Remember me</span>
                    </label>
                    <a class="auth-link" href="{{ route('frontend.forgot.password.index') }}">Forgot password?</a>
                </div>

                <!-- AJAX Form Message Container -->
                <div class="form-message mt-2 mb-3" style="display: none;"></div>

                <button class="auth-submit" type="submit">
                    Sign in
                    <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </form>
            
            <p class="auth-foot">New here? <a class="auth-link" href="{{ route('register') }}">Create an account</a></p>
        </div>
    </section>
</main>
@endsection