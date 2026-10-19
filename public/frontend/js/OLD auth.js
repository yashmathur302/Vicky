$(document).ready(function () {

    // 1. GLOBAL AJAX SECURITY SETUP
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val() // Form me se CSRF token uthayega
        },
        statusCode: {
            // CSRF Token Mismatch / Session Expired
            419: function(xhr) {
                let msgDiv = $('.form-message');
                if(msgDiv.length) {
                    msgDiv.html('<div class="alert alert-danger small"><i class="fas fa-sync fa-spin"></i> Security token expired. Refreshing page to securely continue...</div>').slideDown();
                } else {
                    alert('Security session expired. The page will reload automatically.');
                }
                
                // 2 second baad automatically page reload karke fresh token le aayega
                setTimeout(function() {
                    window.location.reload(); 
                }, 2000);
            },
            
            // Unauthenticated (Logout ho gaya background me)
            401: function(xhr) {
                window.location.href = '/login';
            },

            // Method Not Allowed (Agar redirect ya refresh ki wajah se GET ban gaya ho)
            405: function(xhr) {
                window.location.reload(); // Force reload to correct the state
            }
        }
    });

    // Time Formatter (Seconds ko Hours/Minutes/Seconds me convert karne ke liye)
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

    // Toggle Password Visibility
    $(document).on('click', '.password-toggle', function () {
        let input = $(this).closest('.input-group').find('input');
        let icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });


    // ==========================================
    // 1. SIGN IN FLOW (Login)
    // ==========================================
    if ($('#frontendLoginForm').length > 0) {
        let loginCountdownInterval = null;

        $('#frontendLoginForm').on('submit', function (e) {
            e.preventDefault();

            let form = $(this);
            let submitBtn = form.find('button[type="submit"]');
            let messageBox = form.find('.form-message');
            let formData = form.serialize();
            let originalBtnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Authenticating...');
            messageBox.slideUp().removeClass('text-danger text-success text-warning');

            if (loginCountdownInterval) clearInterval(loginCountdownInterval);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                success: function (response) {
                    submitBtn.removeClass('btn-primary').addClass('btn-success').html('Success <i class="fas fa-check ms-2"></i>');
                    messageBox.html('<div class="alert alert-success p-2 small fw-bold mb-0"><i class="fas fa-check-circle"></i> ' + response.message + '</div>').slideDown();
                    
                    setTimeout(function () {
                        window.location.href = response.redirect;
                    }, 1000);
                },
                error: function (xhr) {
                    // CSRF Token Mismatch (419)
                    if (xhr.status === 419) {
                        submitBtn.prop('disabled', true).html('<i class="fas fa-sync-alt fa-spin"></i> Securing Connection...');
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

                    // Rate Limit (429)
                    if (xhr.status === 429) {
                        let secondsLeft = xhr.responseJSON.locked_seconds;
                        submitBtn.prop('disabled', true).html('<i class="fas fa-lock"></i> Locked');
                        messageBox.html('<div class="alert alert-danger p-2 small fw-bold mb-0"><i class="fas fa-ban"></i> Too many attempts. Try again in <span id="loginTimerText">' + formatTime(secondsLeft) + '</span>.</div>').slideDown();

                        loginCountdownInterval = setInterval(function () {
                            secondsLeft--;
                            $('#loginTimerText').text(formatTime(secondsLeft));
                            if (secondsLeft <= 0) {
                                clearInterval(loginCountdownInterval);
                                submitBtn.prop('disabled', false).html(originalBtnText);
                                messageBox.slideUp();
                            }
                        }, 1000);
                        return;
                    }

                    // Normal Errors (422 / 401)
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    let errorMsg = 'An error occurred. Please try again.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message && !xhr.responseJSON.errors) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors)[0][0]; 
                        }
                    }
                    messageBox.html('<div class="alert alert-danger p-2 small fw-bold mb-0"><i class="fas fa-exclamation-circle"></i> ' + errorMsg + '</div>').slideDown();
                }
            });
        });
    }


    // ==========================================
    // 2. REGISTER FLOW
    // ==========================================
    // if ($('#frontendRegisterForm').length > 0) {
    //     $('#frontendRegisterForm').on('submit', function (e) {
    //         e.preventDefault();

    //         let form = $(this);
    //         let submitBtn = form.find('button[type="submit"]');
    //         let messageBox = form.find('.form-message');
    //         let formData = form.serialize();
    //         let originalBtnText = submitBtn.html();

    //         submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating Account...');
    //         messageBox.slideUp().removeClass('text-danger text-success text-warning');

    //         $.ajax({
    //             url: form.attr('action'),
    //             type: 'POST',
    //             data: formData,
    //             success: function (response) {
    //                 submitBtn.removeClass('btn-primary').addClass('btn-success').html('Registered <i class="fas fa-check ms-2"></i>');
    //                 messageBox.html('<div class="alert alert-success p-2 small fw-bold mb-0"><i class="fas fa-check-circle"></i> ' + response.message + '</div>').slideDown();
                    
    //                 setTimeout(function () {
    //                     window.location.href = response.redirect;
    //                 }, 1500);
    //             },
    //             error: function (xhr) {
    //                 submitBtn.prop('disabled', false).html(originalBtnText);
    //                 let errorMsg = 'An error occurred. Please try again.';
    //                 if (xhr.responseJSON) {
    //                     if (xhr.responseJSON.message && !xhr.responseJSON.errors) {
    //                         errorMsg = xhr.responseJSON.message;
    //                     } else if (xhr.responseJSON.errors) {
    //                         errorMsg = Object.values(xhr.responseJSON.errors)[0][0]; 
    //                     }
    //                 }
    //                 messageBox.html('<div class="alert alert-danger p-2 small fw-bold mb-0"><i class="fas fa-exclamation-circle"></i> ' + errorMsg + '</div>').slideDown();
    //             }
    //         });
    //     });
    // }

    $('#frontendRegisterForm, #frontendLoginForm').on('submit', function(e) {
        e.preventDefault(); // <-- Ye line sabse zaroori hai taaki form page refresh karke submit na ho
        
        let form = $(this);
        let submitBtn = form.find('button[type="submit"]');
        let msgDiv = form.find('.form-message');
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        msgDiv.slideUp();

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method') || 'POST', // Hamesha POST use karega
            data: form.serialize(),
            success: function(response) {
                if(response.redirect) {
                    msgDiv.html('<div class="alert alert-success small"><i class="fas fa-check-circle"></i> ' + response.message + '</div>').slideDown();
                    window.location.href = response.redirect;
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html('Try Again <i class="fas fa-arrow-right ms-2"></i>');
                
                // 419 aur 405 error humara global setup handle kar lega, 
                // isliye yahan sirf validation (422) aur server (500) error dikhayenge
                if (xhr.status === 422 || xhr.status === 500) {
                    let errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Something went wrong. Please try again.';
                    msgDiv.html('<div class="alert alert-danger small"><i class="fas fa-exclamation-circle"></i> ' + errorMessage + '</div>').slideDown();
                }
            }
        });
    });

    // ==========================================
    // 2.1 TERMS & CONDITIONS POPUP LOGIC
    // ==========================================
    // Modal open karne ke liye jab user hyperlink par click kare
    $(document).on('click', '#viewTermsLink', function (e) {
        e.preventDefault();
        let termsModalEl = document.getElementById('termsModal');
        if (termsModalEl) {
            let termsModal = new bootstrap.Modal(termsModalEl);
            termsModal.show();
        }
    });

    // Modal ke andar "I Understand & Agree" button dabane par checkbox auto-check ho jaye
    $(document).on('click', '#modalAcceptTermsBtn', function () {
        $('#terms').prop('checked', true);
    });


    // ==========================================
    // 3. FORGOT PASSWORD FLOW
    // ==========================================
    if ($('#frontendForgotForm').length > 0) {
        let forgotCountdownInterval = null;

        $('#frontendForgotForm').on('submit', function (e) {
            e.preventDefault();

            let form = $(this);
            let submitBtn = form.find('button[type="submit"]');
            let messageBox = form.find('.form-message');
            let formData = form.serialize();
            let originalBtnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending Link...');
            messageBox.slideUp().removeClass('text-danger text-success text-warning');

            if (forgotCountdownInterval) clearInterval(forgotCountdownInterval);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                success: function (response) {
                    submitBtn.removeClass('btn-primary').addClass('btn-success').html('Link Sent <i class="fas fa-check ms-2"></i>');
                    messageBox.html('<div class="alert alert-success p-2 small fw-bold mb-0"><i class="fas fa-check-circle"></i> ' + response.message + '</div>').slideDown();
                    
                    setTimeout(function () {
                        window.location.href = response.redirect;
                    }, 2000);
                },
                error: function (xhr) {
                    // Rate Limit (429)
                    if (xhr.status === 429) {
                        let secondsLeft = xhr.responseJSON.locked_seconds;
                        submitBtn.prop('disabled', true).html('<i class="fas fa-lock"></i> Locked');
                        messageBox.html('<div class="alert alert-danger p-2 small fw-bold mb-0"><i class="fas fa-ban"></i> Too many requests. Try again in <span id="forgotTimerText">' + formatTime(secondsLeft) + '</span>.</div>').slideDown();

                        forgotCountdownInterval = setInterval(function () {
                            secondsLeft--;
                            $('#forgotTimerText').text(formatTime(secondsLeft));
                            if (secondsLeft <= 0) {
                                clearInterval(forgotCountdownInterval);
                                submitBtn.prop('disabled', false).html(originalBtnText);
                                messageBox.slideUp();
                            }
                        }, 1000);
                        return;
                    }

                    // Normal Errors
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    let errorMsg = 'An error occurred. Please try again.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message && !xhr.responseJSON.errors) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors)[0][0]; 
                        }
                    }
                    messageBox.html('<div class="alert alert-danger p-2 small fw-bold mb-0"><i class="fas fa-exclamation-circle"></i> ' + errorMsg + '</div>').slideDown();
                }
            });
        });
    }

    // ==========================================
    // 4. RESET PASSWORD FLOW
    // ==========================================
    if ($('#frontendResetForm').length > 0) {
        $('#frontendResetForm').off('submit').on('submit', function (e) {
            e.preventDefault();

            let form = $(this);
            let password = form.find('#password').val().trim();
            let passwordConfirm = form.find('#password_confirmation').val().trim();
            let submitBtn = form.find('button[type="submit"]');
            let messageBox = form.find('.form-message');
            let originalBtnText = submitBtn.html();

            // 1. Client-side field validations
            if (!password || !passwordConfirm) {
                messageBox.html('<div class="alert alert-danger p-2 small fw-bold mb-0"><i class="fas fa-exclamation-circle"></i> Please fill in all required fields.</div>').slideDown();
                return false;
            }

            if (password !== passwordConfirm) {
                messageBox.html('<div class="alert alert-danger p-2 small fw-bold mb-0"><i class="fas fa-exclamation-circle"></i> The password confirmation does not match.</div>').slideDown();
                return false;
            }

            let passwordErrors = [];
            
            if (password.length < 8) passwordErrors.push("at least 8 characters");
            if (!/[A-Z]/.test(password)) passwordErrors.push("1 uppercase letter");
            if (!/[a-z]/.test(password)) passwordErrors.push("1 lowercase letter");
            if (!/[0-9]/.test(password)) passwordErrors.push("1 number");
            if (!/[^a-zA-Z0-9\s]/.test(password)) passwordErrors.push("1 special character");
            if (/\s/.test(password)) passwordErrors.push("no spaces"); // Space check

            // Agar array me koi bhi error hai, toh popup rokein aur error dikhayein
            if (passwordErrors.length > 0) {
                let errorString = "Password must have: " + passwordErrors.join(", ") + ".";
                messageBox.html('<div class="alert alert-danger p-2 small fw-bold mb-0"><i class="fas fa-exclamation-circle"></i> ' + errorString + '</div>').slideDown();
                return false; // Yahin ruk jayega, popup open nahi hoga
            }

            // 2. Global Dynamic Popup Trigger
            confirmGlobalDecision({
                title: 'Terminate all active sessions?',
                text: 'Do you want to log out of all devices where your account is currently signed in?',
                icon: 'question',
                confirmText: '<i class="fas fa-sign-out-alt me-1"></i> Yes, sign out everywhere',
                denyText: '<i class="fas fa-key me-1"></i> No, update only',
                cancelText: 'Cancel',
                confirmColor: '#d33',
                denyColor: '#3085d6'
            },
            // On Yes (Sign out everywhere)
            function () {
                executeResetSubmit('1');
            },
            // On No (Update password only)
            function () {
                executeResetSubmit('0');
            },
            // On Cancel
            function () {
                // Abort action
            });

            // 3. Execution Helper
            function executeResetSubmit(logoutAllValue) {
                form.find('#logout_all_devices').val(logoutAllValue);

                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
                messageBox.slideUp().removeClass('text-danger text-success text-warning');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        submitBtn.removeClass('btn-primary').addClass('btn-success').html('Updated <i class="fas fa-check ms-2"></i>');
                        messageBox.html('<div class="alert alert-success p-2 small fw-bold mb-0"><i class="fas fa-check-circle"></i> ' + response.message + '</div>').slideDown();

                        setTimeout(function () {
                            window.location.href = response.redirect;
                        }, 1500);
                    },
                    error: function (xhr) {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                        let errorMsg = 'An error occurred. Please try again.';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.errors) {
                                errorMsg = Object.values(xhr.responseJSON.errors)[0][0];
                            } else if (xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                        }
                        messageBox.html('<div class="alert alert-danger p-2 small fw-bold mb-0"><i class="fas fa-exclamation-circle"></i> ' + errorMsg + '</div>').slideDown();
                    }
                });
            }
        });
    }

    // ==========================================
    // 5. REPORT UNAUTHORIZED ACTIVITY FLOW
    // ==========================================
    if ($('#frontendReportForm').length > 0) {
        $('#frontendReportForm').on('submit', function (e) {
            e.preventDefault();

            let form = $(this);
            let submitBtn = form.find('button[type="submit"]');
            let messageBox = form.find('.form-message');
            let originalBtnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
            messageBox.slideUp().removeClass('text-danger text-success text-warning');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function (response) {
                    submitBtn.removeClass('btn-primary').addClass('btn-success').html('Reported <i class="fas fa-check ms-2"></i>');
                    messageBox.html('<div class="alert alert-success p-2 small fw-bold mb-0"><i class="fas fa-check-circle"></i> ' + response.message + '</div>').slideDown();

                    setTimeout(function () {
                        window.location.href = response.redirect;
                    }, 3000);
                },
                error: function (xhr) {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    let errorMsg = 'An error occurred. Please try again.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors)[0][0];
                        } else if (xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                    }
                    messageBox.html('<div class="alert alert-danger p-2 small fw-bold mb-0"><i class="fas fa-exclamation-circle"></i> ' + errorMsg + '</div>').slideDown();
                }
            });
        });
    }

});