@extends('layouts.admin.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="card login-card">
    <div class="card-header text-center pt-4 pb-3 border-0">
        <!-- Logo -->
        <img src="{{ asset('admin/img/logo2.svg') }}" alt="Logo" height="35" class="mb-3">
        <h3 class="card-title fw-bold">Forgot Password</h3>
        <p class="text-muted mb-0">Enter your email to receive a password reset link.</p>
    </div>
    <div class="card-body p-4">

        <!-- Display Session Errors (like Expired Token) -->
        @if(session('error'))
            <div class="alert alert-danger text-center small fw-bold">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            </div>
        @endif
        
        <!-- Forgot Password Form -->
        <form id="forgotPasswordForm" action="{{ route('admin.forgot.password') }}" method="POST">
            @csrf
            <div class="form-group px-0 pt-0">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required autofocus>
                </div>
            </div>
            <div class="form-action mb-3 mt-4">
                <button type="submit" id="resetBtn" class="btn btn-primary w-100 btn-round">Send Reset Link</button>
            </div>
            <div class="text-center mt-3">
                <a href="{{ route('admin.auth.login') }}" class="text-primary small fw-bold text-decoration-none">Back to Sign In</a>
            </div>
        </form>
        <!-- Message Display Container -->
        <div id="resetMessage" class="mt-3 text-center" style="display: none;"></div>

    </div>
</div>
@endsection