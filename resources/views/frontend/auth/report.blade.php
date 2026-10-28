@extends('layouts.frontend.guest')

@section('title', 'Report Activity')

@section('content')
<main class="auth-layout">
    <img class="auth-layout__bg" src="{{ asset('frontend/images/login_bg.jpg') }}" alt="Background">

    <div class="auth-layout__intro">
        <a class="auth-layout__brand" href="{{ route('login') }}">
            <i class="fas fa-layer-group text-warning"></i>
            <span>{{ config('app.name', 'My Project') }}</span>
        </a>
        <p class="auth-layout__kicker">Security Incident</p>
        <p class="auth-layout__headline">Help us protect your account from suspicious access.</p>
    </div>

    <section class="auth-glass">
        <div>
            <h1 class="auth-title">Report Request</h1>
            <p class="auth-sub">Did not request a password reset? Describe what happened and our team will investigate.</p>

            <form id="frontendReportForm" action="{{ route('account.report.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="hash" value="{{ $hash }}">

                <div class="auth-field">
                    <label class="auth-label">Account Email</label>
                    <input class="auth-input" type="text" value="{{ $user->email }}" disabled style="opacity: 0.7;">
                </div>

                <div class="auth-field">
                    <label class="auth-label" for="description">Incident Description <span class="text-danger">*</span></label>
                    <textarea class="auth-input" id="description" name="description" rows="4" style="height: auto; border-radius: 14px; padding: 12px;" required placeholder="Tell us what you noticed (e.g. I did not request this password reset email, unknown IP attempt)..."></textarea>
                </div>

                <div class="form-message mt-2 mb-3" style="display: none;"></div>

                <button class="auth-submit" type="submit" style="background: #dc3545; color: #fff;">
                    Submit Report
                    <i class="fas fa-shield-alt ms-2"></i>
                </button>
            </form>

            <p class="auth-foot mt-4"><a class="auth-link" href="{{ route('login') }}">Back to Sign In</a></p>
        </div>
    </section>
</main>
@endsection

