@extends('layouts.frontend.guest')

@section('title', 'Sign In')

@section('content')
<main class="cl-login04">
    <img class="cl-login04__bg" src="{{ asset('frontend/images/login_bg.jpg') }}" alt="Background">

    <!-- LEFT SECTION: INTRO -->
    <div class="cl-login04__intro">
        <a class="cl-login04__brand" href="{{ route('login') }}">
            <i class="fas fa-layer-group text-warning"></i>
            <span>{{ config('app.name', 'My Project') }}</span>
        </a>
        <p class="cl-login04__kicker">Welcome to our platform</p>
        <p class="cl-login04__headline">Every tool your team has ever loved, in one place.</p>
    </div>

    <!-- RIGHT SECTION: LOGIN FORM GLASS CARD -->
    <section class="cl-login04__glass">
        <div>
            <h1 class="cl-login04__title">Sign in</h1>
            <p class="cl-login04__sub">Good to see you again.</p>

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

            <!-- MAIN FORM (Integrated with auth OLD.js) -->
            <form id="frontendLoginForm" class="cl-login04__form" action="{{ route('login.submit') }}" method="POST">
                @csrf
                
                <div class="cl-login04__field">
                    <label class="cl-login04__label" for="login_id">Email or Username <span class="text-danger">*</span></label>
                    <input class="cl-login04__input" id="login_id" name="login_id" type="text" required placeholder="you@example.com">
                    {{-- <input class="cl-login04__input" id="login_id" name="login_id" type="text" autocomplete="username" required placeholder="you@example.com"> --}}
                </div>

                <div class="cl-login04__field">
                    <label class="cl-login04__label" for="password">Password <span class="text-danger">*</span></label>
                    <div class="cl-login04__pw input-group">
                        <input class="cl-login04__input" id="password" style="border-radius: 14px" name="password" type="password" required placeholder="Enter your password">
                        {{-- <input class="cl-login04__input" id="password" style="border-radius: 14px" name="password" type="password" autocomplete="current-password" required placeholder="Enter your password"> --}}
                        
                        <button class="cl-login04__eye password-toggle" type="button">
                            <i class="fas fa-eye" style="color: black;"></i>
                        </button>
                    </div>
                </div>

                <div class="cl-login04__row">
                    <label class="cl-login04__check">
                        <input type="checkbox" name="remember"> <span>Remember me</span>
                    </label>
                    <a class="cl-login04__link" href="{{ route('frontend.forgot.password.index') }}">Forgot password?</a>
                </div>

                <!-- AJAX Form Message Container -->
                <div class="form-message mt-2 mb-3" style="display: none;"></div>

                <button class="cl-login04__submit" type="submit">
                    Sign in
                    <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </form>

            {{-- <p class="cl-login04__or"><span>or continue with</span></p>
            <div class="cl-login04__social">
                <button class="cl-login04__icon-btn" type="button" aria-label="Continue with Google">
                    <i class="fab fa-google"></i>
                </button>
                <button class="cl-login04__icon-btn" type="button" aria-label="Continue with Apple">
                    <i class="fab fa-apple fs-5"></i>
                </button>
                <button class="cl-login04__icon-btn" type="button" aria-label="Continue with Facebook">
                    <i class="fab fa-facebook-f"></i>
                </button>
            </div> --}}
            
            <p class="cl-login04__foot">New here? <a class="cl-login04__link" href="{{ route('register') }}">Create an account</a></p>
        </div>
    </section>
</main>
@endsection