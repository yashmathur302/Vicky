@extends('layouts.frontend.guest')

@section('title', 'Create Account')

@section('content')
<main class="cl-login04">
    <!-- Background Image -->
    <img class="cl-login04__bg" src="{{ asset('frontend/images/login_bg.jpg') }}" alt="Background">

    <!-- LEFT SECTION: INTRO -->
    <div class="cl-login04__intro">
        <a class="cl-login04__brand" href="#">
            <i class="fas fa-layer-group text-warning"></i>
            <span>{{ config('app.name', 'My Project') }}</span>
        </a>
        <p class="cl-login04__kicker">Welcome to our platform</p>
        <p class="cl-login04__headline">Every tool your team has ever loved, in one place.</p>
        <!-- <ul class="cl-login04__chips">
            <li><span style="background:#fb7185"></span><span style="background:#f97316"></span><span style="background:#facc15"></span>Seamless UI</li>
            <li><span style="background:#0ea5e9"></span><span style="background:#6366f1"></span><span style="background:#a855f7"></span>Secure Data</li>
        </ul> -->
    </div>

    <!-- RIGHT SECTION: REGISTER FORM GLASS CARD -->
    <section class="cl-login04__glass">
        <div>
            <h1 class="cl-login04__title">Create an Account</h1>
            <p class="cl-login04__sub">Join us and experience secure data handling.</p>

            <form id="frontendRegisterForm" class="cl-login04__form" action="{{ route('register.submit') }}" method="POST">
                @csrf
                
                <div class="cl-login04__field">
                    <label class="cl-login04__label" for="name">Full Name <span class="text-danger">*</span></label>
                    <input class="cl-login04__input" id="name" name="name" type="text" autocomplete="name" required placeholder="e.g. John Doe" autofocus>
                </div>

                <div class="cl-login04__field">
                    <label class="cl-login04__label" for="username">Username <span class="text-danger">*</span></label>
                    <input class="cl-login04__input" id="username" name="username" type="text" autocomplete="username" required placeholder="e.g. johndoe123">
                </div>

                <div class="cl-login04__field">
                    <label class="cl-login04__label" for="email">Email Address <span class="text-danger">*</span></label>
                    <input class="cl-login04__input" id="email" name="email" type="email" autocomplete="email" required placeholder="you@example.com">
                </div>

                <div class="cl-login04__field">
                    <label class="cl-login04__label" for="password">Password <span class="text-danger">*</span></label>
                    <!-- "input-group" class rakhi gayi hai auth.js compatibility ke liye -->
                    <div class="cl-login04__pw input-group">
                        <input class="cl-login04__input" style="border-radius: 14px" id="password" name="password" type="password" autocomplete="new-password" required placeholder="Min 8 characters">
                        <button class="cl-login04__eye password-toggle" type="button">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="form-text text-light mt-1" style="font-size: 11px; opacity: 0.8;">
                        Must contain a capital letter, small letter, number, and special character.
                    </div>
                </div>

                <div class="cl-login04__field">
                    <label class="cl-login04__label" for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                    <div class="cl-login04__pw input-group">
                        <input class="cl-login04__input" style="border-radius: 14px" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required placeholder="Confirm your password">
                        <button class="cl-login04__eye password-toggle" type="button">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- TERMS AND CONDITIONS CHECKBOX -->
                <div class="cl-login04__row mt-3 mb-4">
                    <label class="cl-login04__check" for="terms" style="cursor: pointer; user-select: none;">
                        <input type="checkbox" name="terms" id="terms" value="1" style="cursor: pointer;" required> 
                        <span>I agree to the <a href="javascript:void(0)" class="cl-login04__link text-decoration-underline" id="viewTermsLink">Terms &amp; Conditions</a> <span class="text-danger">*</span></span>
                    </label>
                </div>

                <!-- AJAX Form Message Container -->
                <div class="form-message mt-2 mb-3" style="display: none;"></div>

                <button class="cl-login04__submit" type="submit">
                    Sign Up
                    <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </form>
            
            <p class="cl-login04__foot">Already have an account? <a class="cl-login04__link" href="{{ route('login') }}">Sign In</a></p>
        </div>
    </section>
</main>

<!-- TERMS & CONDITIONS MODAL -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: rgba(20, 24, 33, 0.95); backdrop-filter: blur(16px); color: #fff; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 16px;">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold" id="termsModalLabel"><i class="fas fa-file-contract text-warning me-2"></i>Terms &amp; Conditions</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="font-size: 14px; line-height: 1.6; max-height: 60vh; overflow-y: auto;">
                <h6 class="fw-bold text-warning mb-2">1. Acceptance of Terms</h6>
                <p>By creating an account on {{ config('app.name') }}, you agree to comply with and be bound by all applicable policies, guidelines, and security protocols.</p>

                <h6 class="fw-bold text-warning mb-2 mt-3">2. Account Responsibility &amp; Security</h6>
                <p>You are solely responsible for maintaining the confidentiality of your login credentials and for all activities that occur under your account.</p>

                <h6 class="fw-bold text-warning mb-2 mt-3">3. Data Privacy</h6>
                <p>We respect your privacy. Your personal information, encrypted data, and login telemetry will be processed in accordance with our data protection policies.</p>

                <h6 class="fw-bold text-warning mb-2 mt-3">4. Prohibited Activities</h6>
                <p>Any attempt to brute-force, reverse engineer, bypass authentication rate limits, or perform unauthorized administrative actions will lead to immediate account termination.</p>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal" id="modalAcceptTermsBtn">I Understand &amp; Agree</button>
            </div>
        </div>
    </div>
</div>
@endsection