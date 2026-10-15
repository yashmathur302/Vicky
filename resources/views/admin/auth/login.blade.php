@extends('layouts.admin.auth')

@section('title', 'Sign In')

@section('content')
<div class="card login-card">
    <div class="card-header text-center pt-4 pb-3 border-0">
        <!-- Logo -->
        <img src="{{ asset('admin/img/logo2.svg') }}" alt="Logo" height="35" class="mb-3">
        <h3 class="card-title fw-bold">Admin Sign In</h3>
    </div>
    <div class="card-body p-4">
        
        <!-- Login Form -->
        <form id="adminLoginForm" action="{{ route('admin.login') }}" method="POST">
            @csrf

            <div class="form-group px-0 pt-0">
                <label for="login_id" class="form-label">Email<span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" id="login_id" name="login_id" placeholder="Enter your email" required autofocus>
                </div>
            </div>

            <div class="form-group px-0">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label mb-0">Password <span class="text-danger">*</span></label>
                    <a href="{{ route('admin.forgot.password.index') }}" class="text-primary small text-decoration-none">Forgot Password?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    <!-- Eye Icon Toggle -->
                    <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-check ps-4 mt-2 mb-3">
                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                <label class="form-check-label" for="remember">
                    Remember Me
                </label>
            </div>

            <div class="form-action mb-3">
                <button type="submit" id="loginBtn" class="btn btn-primary w-100 btn-round">Sign In</button>
            </div>
        </form>

        <!-- Message Display Container -->
        <div id="loginMessage" class="mt-3 text-center" style="display: none;"></div>

    </div>
</div>
@endsection

{{-- @push('scripts')
<script>
    $(document).ready(function() {
        
        let countdownInterval = null; // Timer variable

        $('#adminLoginForm').on('submit', function(e) {
            e.preventDefault(); 
            
            let form = $(this);
            let submitBtn = $('#loginBtn');
            let messageBox = $('#loginMessage');
            let formData = form.serialize();

            // UI State: Submitting
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Authenticating...');
            messageBox.slideUp().removeClass('text-danger text-success');
            
            // Purana koi timer chal raha ho to use clear kardenge
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    // UI State: Success
                    submitBtn.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i> Success');
                    messageBox.html('<small class="fw-bold text-success">' + response.message + '</small>').slideDown();
                    
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1000);
                },
                error: function(xhr) {
                    let errorMsg = 'An error occurred. Please try again.';
                    
                    //rate limit Lockout 
                    if (xhr.status === 429) {
                        let secondsLeft = xhr.responseJSON.locked_seconds;
                        
                        // Button ko disabled hi rakhenge
                        submitBtn.prop('disabled', true).html('<i class="fas fa-lock"></i> Locked');
                        
                        // Real-time Countdown Timer shuru karein
                        messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-ban"></i> Too many attempts. Try again in <span id="timerText">' + secondsLeft + '</span> seconds.</small>').slideDown();

                        countdownInterval = setInterval(function() {
                            secondsLeft--;
                            $('#timerText').text(secondsLeft);

                            // Jab timer 0 ho jaye, to form ko wapas normal state me le aao
                            if (secondsLeft <= 0) {
                                clearInterval(countdownInterval);
                                submitBtn.prop('disabled', false).html('Sign In');
                                messageBox.slideUp();
                            }
                        }, 1000); // Har 1000ms (1 second) me update hoga

                    } 
                    // 2. Agar Normal Validation/Wrong Password Error hai (Status 422)
                    else if (xhr.status === 422 || xhr.status === 401) {
                        submitBtn.prop('disabled', false).html('Sign In');
                        errorMsg = xhr.responseJSON.message || xhr.responseJSON.errors.login_id[0];
                        messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-exclamation-circle"></i> ' + errorMsg + '</small>').slideDown();
                    } 
                    // 3. Koi Server Crash (500) ho jaye
                    else {
                        submitBtn.prop('disabled', false).html('Sign In');
                        messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-exclamation-triangle"></i> ' + errorMsg + '</small>').slideDown();
                    }
                }
            });
        });
    });
</script>
@endpush --}}