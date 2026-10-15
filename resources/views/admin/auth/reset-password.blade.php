@extends('layouts.admin.auth')

@section('title', 'Set New Password')

@section('content')
<div class="card login-card">
    <div class="card-header text-center pt-4 pb-3 border-0">
        <img src="{{ asset('admin/img/logo.svg') }}" alt="Logo" height="35" class="mb-3">
        <h3 class="card-title fw-bold">Create New Password</h3>
        <p class="text-muted mb-0">Enter your new password below.</p>
    </div>
    <div class="card-body p-4">
        
        <form id="resetPasswordForm" action="{{ route('admin.password.reset') }}" method="POST">
            @csrf
            
            <!-- Hidden fields to pass token and email to the backend -->
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="logout_all_devices" id="logout_all_devices" value="0">

            <div class="form-group px-0 pt-0">
                <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Min 8 characters" required autofocus>
                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="form-text text-muted mt-1 small">
                    Use capital letter, a small letter, contain no spaces, at least 1 number, and 1 special character to make a secure password.
                </div>
            </div>

            <div class="form-group px-0">
                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm your new password" required>
                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-action mb-3 mt-4">
                <button type="submit" id="updateBtn" class="btn btn-primary w-100 btn-round">Update Password</button>
            </div>
        </form>

        <div id="updateMessage" class="mt-3 text-center" style="display: none;"></div>
    </div>
</div>
@endsection