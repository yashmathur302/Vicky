@extends('layouts.frontend.guest')

@section('title', 'Reset Password')

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
        <p class="auth-layout__kicker">Secure Your Account</p>
        <p class="auth-layout__headline">Set a new strong password for your account.</p>
        {{-- <ul class="auth-layout__chips">
            <li><span style="background:#facc15"></span><span style="background:#ef4444"></span>Privacy First</li>
            <li><span style="background:#10b981"></span>Encrypted Data</li>
        </ul> --}}
    </div>

    <!-- RIGHT SECTION: FORM GLASS CARD -->
    <section class="auth-glass">
        <div>
            <h1 class="auth-title">Create New Password</h1>
            <p class="auth-sub">Your new password must be different from previous used passwords.</p>

            <form id="frontendResetForm" action="{{ route('frontend.password.reset') }}" method="POST">
                @csrf
                <!-- Token aur Email hidden bhejna zaroori hota hai password reset process me -->
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="logout_all_devices" id="logout_all_devices" value="0">
                
                {{-- <div class="auth-field">
                    <label class="auth-label" for="email">Email Address <span class="text-danger">*</span></label>
                    <input class="auth-input" id="email" name="email" type="email" autocomplete="email" required placeholder="Verify your email address">
                </div> --}}

                <div class="auth-field">
                    <label class="auth-label" for="password">New Password <span class="text-danger">*</span></label>
                    <!-- "input-group" auth.js toggle ke liye -->
                    <div class="auth-pw-wrapper input-group">
                        <input class="auth-input" id="password" name="password" type="password" autocomplete="new-password" required placeholder="Min 8 characters" autofocus style="border-radius: 14px">
                        <button class="auth-eye password-toggle" type="button" aria-label="Toggle Password Visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="form-text text-light mt-1" style="font-size: 11px; opacity: 0.8;">
                        Must contain a capital letter, small letter, number, and special character.
                    </div>
                </div>

                <div class="auth-field">
                    <label class="auth-label" for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                    <div class="auth-pw-wrapper input-group">
                        <input class="auth-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required placeholder="Confirm new password" style="border-radius: 14px">
                        <button class="auth-eye password-toggle" type="button" aria-label="Toggle Password Visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- AJAX Form Message Container -->
                <div class="form-message mt-2 mb-3" style="display: none;"></div>

                <button class="auth-submit" type="submit">
                    Reset Password
                    <i class="fas fa-lock ms-2"></i>
                </button>
            </form>
        </div>
    </section>
</main>
@endsection