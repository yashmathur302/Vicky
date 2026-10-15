{{-- @extends('layouts.frontend.app') --}}
@extends('layouts.frontend.guest')

@section('title', 'Forgot Password')

@section('content')
<main class="cl-login04">
    <!-- Naya background lagayein -->
    <img class="cl-login04__bg" src="{{ asset('frontend/images/login_bg.jpg') }}" alt="Background">

    <!-- LEFT SECTION: INTRO -->
    <div class="cl-login04__intro">
        <a class="cl-login04__brand" href="#">
            <i class="fas fa-layer-group text-warning"></i>
            <span>{{ config('app.name', 'My Project') }}</span>
        </a>
        <p class="cl-login04__kicker">Account Recovery</p>
        <p class="cl-login04__headline">Lost your access? Let's get you back in.</p>
    </div>

    <!-- RIGHT SECTION: FORM GLASS CARD -->
    <section class="cl-login04__glass">
        <div>
            <h1 class="cl-login04__title">Forgot Password?</h1>
            <p class="cl-login04__sub">No worries! Enter your email address below and we'll send you a password reset link.</p>

            <!-- Display Session Errors (like Expired Token) -->
            @if(session('error'))
                <div class="alert alert-danger text-center small fw-bold">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                </div>
            @endif

            <form id="frontendForgotForm" class="cl-login04__form" action="{{ route('frontend.forgot.password') }}" method="POST">
                @csrf
                
                <div class="cl-login04__field">
                    <label class="cl-login04__label" for="email">Email Address <span class="text-danger">*</span></label>
                    <input class="cl-login04__input" id="email" name="email" type="email" autocomplete="email" required placeholder="you@example.com" autofocus>
                </div>

                <!-- AJAX Form Message Container -->
                <div class="form-message mt-2 mb-3 form-message" style="display: none;"></div>

                <button class="cl-login04__submit" type="submit">
                    Send Reset Link
                    <i class="fas fa-paper-plane ms-2"></i>
                </button>
            </form>

            <p class="cl-login04__foot mt-4">Remembered your password? <a class="cl-login04__link" href="{{ route('login') }}">Back to Sign In</a></p>
        </div>
    </section>
</main>
@endsection