$(document).ready(function() {

    // ==========================================
    // HELPER FUNCTION: Time Formatter
    // Converts raw seconds into readable "X hours Y minutes Z seconds"
    // ==========================================
    function formatTime(totalSeconds) {
        let h = Math.floor(totalSeconds / 3600);
        let m = Math.floor((totalSeconds % 3600) / 60);
        let s = totalSeconds % 60;

        let parts = [];
        if (h > 0) parts.push(h + (h === 1 ? ' hour' : ' hours'));
        if (m > 0) parts.push(m + (m === 1 ? ' minute' : ' minutes'));
        if (s >= 0) parts.push(s + (s === 1 ? ' second' : ' seconds'));

        return parts.join(' ');
    }

    // ==========================================
    // 1. FORGOT PASSWORD FLOW
    // ==========================================
    if ($('#forgotPasswordForm').length > 0) {
        
        let forgotCountdownInterval = null;

        $('#forgotPasswordForm').on('submit', function(e) {
            e.preventDefault(); 
            
            let form = $(this);
            let submitBtn = $('#resetBtn');
            let messageBox = $('#resetMessage');
            let formData = form.serialize();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
            messageBox.slideUp().removeClass('text-danger text-success');

            if (forgotCountdownInterval) {
                clearInterval(forgotCountdownInterval);
            }

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    submitBtn.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i> Link Sent');
                    messageBox.html('<small class="fw-bold text-success">' + response.message + '</small>').slideDown();
                    
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 2000);
                },
                error: function(xhr) {
                    // RATE LIMIT LOCKOUT (Status 429)
                    if (xhr.status === 429) {
                        let secondsLeft = xhr.responseJSON.locked_seconds;

                        submitBtn.prop('disabled', true).html('<i class="fas fa-lock"></i> Locked');
                        
                        // NAYA: Hardcoded "seconds" word HTML se hata diya aur formatTime() use kiya
                        messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-ban"></i> Too many requests. Try again in <span id="forgotTimerText">' + formatTime(secondsLeft) + '</span>.</small>').slideDown();

                        forgotCountdownInterval = setInterval(function() {
                            secondsLeft--;
                            // NAYA: Timer text ko har second format karke update karenge
                            $('#forgotTimerText').text(formatTime(secondsLeft));

                            if (secondsLeft <= 0) {
                                clearInterval(forgotCountdownInterval);
                                submitBtn.prop('disabled', false).html('Send Reset Link');
                                messageBox.slideUp();
                            }
                        }, 1000);
                        return; 
                    }
                    
                    // NORMAL ERROR
                    submitBtn.prop('disabled', false).html('Send Reset Link');
                    let errorMsg = xhr.responseJSON.message || 'An error occurred. Please try again.';
                    messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-exclamation-circle"></i> ' + errorMsg + '</small>').slideDown();
                }
            });
        });
    }

    // ==========================================
    // 2. SIGN IN FLOW
    // ==========================================
    if ($('#adminLoginForm').length > 0) {
        
        let loginCountdownInterval = null; 

        $('#adminLoginForm').on('submit', function(e) {
            e.preventDefault(); 
            
            let form = $(this);
            let submitBtn = $('#loginBtn');
            let messageBox = $('#loginMessage');
            let formData = form.serialize();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Authenticating...');
            messageBox.slideUp().removeClass('text-danger text-success');
            
            if (loginCountdownInterval) {
                clearInterval(loginCountdownInterval);
            }

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    submitBtn.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i> Success');
                    messageBox.html('<small class="fw-bold text-success">' + response.message + '</small>').slideDown();
                    
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1000);
                },
                error: function(xhr) {
                    // CSRF TOKEN MISMATCH / SESSION EXPIRED (Status 419)
                    if (xhr.status === 419) {
                        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Securing Connection...');
                        
                        $.get(window.location.href, function(htmlText) {
                            let parser = new DOMParser();
                            let doc = parser.parseFromString(htmlText, "text/html");
                            let newToken = doc.querySelector('meta[name="csrf-token"]');
                            
                            if (newToken && newToken.getAttribute('content')) {
                                let tokenValue = newToken.getAttribute('content');
                                $('meta[name="csrf-token"]').attr('content', tokenValue);
                                form.find('input[name="_token"]').val(tokenValue);
                                form.trigger('submit');
                            } else {
                                window.location.reload();
                            }
                        }).fail(function() {
                            window.location.reload();
                        });
                        
                        return;
                    }

                    // RATE LIMIT LOCKOUT (Status 429)
                    if (xhr.status === 429) {
                        let secondsLeft = xhr.responseJSON.locked_seconds;
                        
                        submitBtn.prop('disabled', true).html('<i class="fas fa-lock"></i> Locked');
                        
                        // NAYA: Hardcoded "seconds" hataya aur dynamic formatting lagayi
                        messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-ban"></i> Too many attempts. Try again in <span id="loginTimerText">' + formatTime(secondsLeft) + '</span>.</small>').slideDown();

                        loginCountdownInterval = setInterval(function() {
                            secondsLeft--;
                            // NAYA: Text update with formatting
                            $('#loginTimerText').text(formatTime(secondsLeft));

                            if (secondsLeft <= 0) {
                                clearInterval(loginCountdownInterval);
                                submitBtn.prop('disabled', false).html('Sign In');
                                messageBox.slideUp();
                            }
                        }, 1000);
                        return;
                    } 
                    
                    // NORMAL VALIDATION ERROR (Status 422 or 401)
                    submitBtn.prop('disabled', false).html('Sign In');
                    
                    let errorMsg = 'An error occurred. Please try again.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message && !xhr.responseJSON.errors) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors)[0][0]; 
                        }
                    }
                    
                    messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-exclamation-circle"></i> ' + errorMsg + '</small>').slideDown();
                }
            });
        });
    }

    // ==========================================
    // 3. RESET PASSWORD FLOW (With Multi-Device Logout Popup)
    // ==========================================
    if ($('#resetPasswordForm').length > 0) {
        
        $('#resetPasswordForm').on('submit', function(e) {
            e.preventDefault(); 
            
            let form = $(this);
            let password = form.find('#password').val();
            let passwordConfirm = form.find('#password_confirmation').val();
            let submitBtn = $('#updateBtn');
            let messageBox = $('#updateMessage');

            // 1. Client-Side Pre-Validation (Popup se pehle check karein)
            if (!password || !passwordConfirm) {
                messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-exclamation-circle"></i> Please fill in all required fields.</small>').slideDown();
                return;
            }

            if (password !== passwordConfirm) {
                messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-exclamation-circle"></i> The password confirmation does not match.</small>').slideDown();
                return;
            }

            // 2. SweetAlert2 Confirmation Dialog
            Swal.fire({
                title: 'Terminate all active sessions?',
                text: 'Do you want to log out of all devices where your admin account is currently signed in?',
                icon: 'question',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-sign-out-alt me-1"></i> Yes, sign out everywhere',
                denyButtonText: '<i class="fas fa-key me-1"></i> No, update only',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d33',
                denyButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                allowOutsideClick: false
            }).then((result) => {
                // Agar user ne 'Cancel' kiya, toh request abort kar dein
                if (result.isDismissed) {
                    return;
                }

                // Yes click hua toh value 1, No click hua toh value 0
                let logoutAll = result.isConfirmed ? '1' : '0';
                form.find('#logout_all_devices').val(logoutAll);

                // 3. Submit Form via AJAX
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
                messageBox.slideUp().removeClass('text-danger text-success');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        submitBtn.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i> Updated');
                        messageBox.html('<small class="fw-bold text-success">' + response.message + '</small>').slideDown();
                        
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1500);
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html('Update Password');
                        
                        let errorMsg = 'An error occurred. Please try again.';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.errors) {
                                errorMsg = Object.values(xhr.responseJSON.errors)[0][0];
                            } else if (xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                        }
                        
                        messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-exclamation-circle"></i> ' + errorMsg + '</small>').slideDown();
                    }
                });
            });
        });
    }

    // ==========================================
    // 3. RESET PASSWORD FLOW
    // ==========================================
    // if ($('#resetPasswordForm').length > 0) {
        
    //     $('#resetPasswordForm').on('submit', function(e) {
    //         e.preventDefault(); 
            
    //         let form = $(this);
    //         let submitBtn = $('#updateBtn');
    //         let messageBox = $('#updateMessage');

    //         submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
    //         messageBox.slideUp().removeClass('text-danger text-success');

    //         $.ajax({
    //             url: form.attr('action'),
    //             type: 'POST',
    //             data: form.serialize(),
    //             success: function(response) {
    //                 submitBtn.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i> Updated');
    //                 messageBox.html('<small class="fw-bold text-success">' + response.message + '</small>').slideDown();
                    
    //                 setTimeout(function() {
    //                     window.location.href = response.redirect;
    //                 }, 1500);
    //             },
    //             error: function(xhr) {
    //                 submitBtn.prop('disabled', false).html('Update Password');
                    
    //                 let errorMsg = 'An error occurred. Please try again.';
                    
    //                 if (xhr.responseJSON) {
    //                     if (xhr.responseJSON.errors) {
    //                         errorMsg = Object.values(xhr.responseJSON.errors)[0][0];
    //                     } else if (xhr.responseJSON.message) {
    //                         errorMsg = xhr.responseJSON.message;
    //                     }
    //                 }
                    
    //                 messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-exclamation-circle"></i> ' + errorMsg + '</small>').slideDown();
    //             }
    //         });
    //     });
    // }

});