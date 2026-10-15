@extends('layouts.frontend.guest')

@section('title', 'Report Activity')

@section('content')
<main class="cl-login04">
    <img class="cl-login04__bg" src="{{ asset('frontend/images/login_bg.jpg') }}" alt="Background">

    <div class="cl-login04__intro">
        <a class="cl-login04__brand" href="{{ route('login') }}">
            <i class="fas fa-layer-group text-warning"></i>
            <span>{{ config('app.name', 'My Project') }}</span>
        </a>
        <p class="cl-login04__kicker">Security Incident</p>
        <p class="cl-login04__headline">Help us protect your account from suspicious access.</p>
    </div>

    <section class="cl-login04__glass">
        <div>
            <h1 class="cl-login04__title">Report Request</h1>
            <p class="cl-login04__sub">Did not request a password reset? Describe what happened and our team will investigate.</p>

            <form id="frontendReportForm" class="cl-login04__form" action="{{ route('account.report.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="hash" value="{{ $hash }}">

                <div class="cl-login04__field">
                    <label class="cl-login04__label">Account Email</label>
                    <input class="cl-login04__input" type="text" value="{{ $user->email }}" disabled style="opacity: 0.7;">
                </div>

                <div class="cl-login04__field">
                    <label class="cl-login04__label" for="description">Incident Description <span class="text-danger">*</span></label>
                    <textarea class="cl-login04__input" id="description" name="description" rows="4" style="height: auto; border-radius: 14px; padding: 12px;" required placeholder="Tell us what you noticed (e.g. I did not request this password reset email, unknown IP attempt)..."></textarea>
                </div>

                <div class="form-message mt-2 mb-3" style="display: none;"></div>

                <button class="cl-login04__submit" type="submit" style="background: #dc3545;">
                    Submit Report
                    <i class="fas fa-shield-alt ms-2"></i>
                </button>
            </form>

            <p class="cl-login04__foot mt-4"><a class="cl-login04__link" href="{{ route('login') }}">Back to Sign In</a></p>
        </div>
    </section>
</main>
@endsection

@push('scripts')
    <script src="{{ asset('frontend/js/auth.js') }}?v={{ time() }}"></script>
@endpush