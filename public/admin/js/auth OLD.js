$(document).ready(function() {



    // 1. FORGOT PASSWORD FLOW

    if ($('#forgotPasswordForm').length > 0) {

        

        let forgotCountdownInterval = null;



        $('#forgotPasswordForm').on('submit', function(e) {

            e.preventDefault(); 

            

            let form = $(this);

            let submitBtn = $('#resetBtn');

            let messageBox = $('#resetMessage');

            let formData = form.serialize();



            // UI State: Submitting

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

            messageBox.slideUp().removeClass('text-danger text-success');



            // Clear existing timer if any

            if (forgotCountdownInterval) {

                clearInterval(forgotCountdownInterval);

            }



            $.ajax({

                url: form.attr('action'),

                type: 'POST',

                data: formData,

                success: function(response) {

                    // UI State: Success

                    submitBtn.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i> Link Sent');

                    messageBox.html('<small class="fw-bold text-success">' + response.message + '</small>').slideDown();

                    

                    // Redirect to Sign In page after 2 seconds

                    setTimeout(function() {

                        window.location.href = response.redirect;

                    }, 2000);

                },

                error: function(xhr) {

                    // RATE LIMIT LOCKOUT (Status 429)

                    if (xhr.status === 429) {

                        let secondsLeft = xhr.responseJSON.locked_seconds;



                        submitBtn.prop('disabled', true).html('<i class="fas fa-lock"></i> Locked');

                        messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-ban"></i> Too many requests. Try again in <span id="forgotTimerText">' + secondsLeft + '</span> seconds.</small>').slideDown();



                        // Start Countdown Timer

                        forgotCountdownInterval = setInterval(function() {

                            secondsLeft--;

                            $('#forgotTimerText').text(secondsLeft);



                            if (secondsLeft <= 0) {

                                clearInterval(forgotCountdownInterval);

                                submitBtn.prop('disabled', false).html('Send Reset Link');

                                messageBox.slideUp();

                            }

                        }, 1000);

                        return; 

                    }

                    

                    // NORMAL ERROR (Status 422 or 500)

                    submitBtn.prop('disabled', false).html('Send Reset Link');

                    let errorMsg = xhr.responseJSON.message || 'An error occurred. Please try again.';

                    messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-exclamation-circle"></i> ' + errorMsg + '</small>').slideDown();

                }

            });

        });

    }



    // 2. SIGN IN FLOW (Iske andar login.blade ka code aayega)

    if ($('#adminLoginForm').length > 0) {

        

        let loginCountdownInterval = null; 



        $('#adminLoginForm').on('submit', function(e) {

            e.preventDefault(); 

            

            let form = $(this);

            let submitBtn = $('#loginBtn');

            let messageBox = $('#loginMessage');

            let formData = form.serialize();



            // UI State: Submitting

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Authenticating...');

            messageBox.slideUp().removeClass('text-danger text-success');

            

            // Clear existing timer

            if (loginCountdownInterval) {

                clearInterval(loginCountdownInterval);

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



                    // CSRF TOKEN MISMATCH / SESSION EXPIRED (Status 419)

                    if (xhr.status === 419) {

                        

                        // 1. UI ko processing state me rakhein, user ko error na dikhayen

                        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Securing Connection...');

                        

                        // 2. Background me current page ko wapas fetch karein naya token laane ke liye

                        $.get(window.location.href, function(htmlText) {

                            

                            // Safe HTML Parsing (Without loading external images/scripts)

                            let parser = new DOMParser();

                            let doc = parser.parseFromString(htmlText, "text/html");

                            let newToken = doc.querySelector('meta[name="csrf-token"]');

                            

                            if (newToken && newToken.getAttribute('content')) {

                                let tokenValue = newToken.getAttribute('content');

                                

                                // 3. Naya token DOM aur Form me update karein

                                $('meta[name="csrf-token"]').attr('content', tokenValue);

                                form.find('input[name="_token"]').val(tokenValue);

                                

                                // 4. Form ko automatically dobara submit kar dein (Auto-Retry)

                                form.trigger('submit');

                            } else {

                                // Fallback: Agar token na mile toh hi reload karein

                                window.location.reload();

                            }

                        }).fail(function() {

                            window.location.reload();

                        });

                        

                        return;

                    }

                    

                    // CSRF TOKEN MISMATCH / SESSION EXPIRED (Status 419)

                    // if (xhr.status === 419) {

                    //     submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Refreshing...');

                    //     messageBox.html('<small class="fw-bold text-warning"><i class="fas fa-sync-alt"></i> Security token expired. Refreshing page...</small>').slideDown();

                        

                    //     // Automatically reload the page to get a fresh CSRF token

                    //     setTimeout(function() {

                    //         window.location.reload();

                    //     }, 1500);

                        

                    //     return;

                    // }



                    // RATE LIMIT LOCKOUT (Status 429)

                    if (xhr.status === 429) {

                        let secondsLeft = xhr.responseJSON.locked_seconds;

                        

                        submitBtn.prop('disabled', true).html('<i class="fas fa-lock"></i> Locked');

                        messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-ban"></i> Too many attempts. Try again in <span id="loginTimerText">' + secondsLeft + '</span> seconds.</small>').slideDown();



                        loginCountdownInterval = setInterval(function() {

                            secondsLeft--;

                            $('#loginTimerText').text(secondsLeft);



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

                    

                    // Safely extract the first error message regardless of the field name

                    let errorMsg = 'An error occurred. Please try again.';

                    if (xhr.responseJSON) {

                        if (xhr.responseJSON.message && !xhr.responseJSON.errors) {

                            errorMsg = xhr.responseJSON.message;

                        } else if (xhr.responseJSON.errors) {

                            // Automatically picks the first error string from the errors object

                            errorMsg = Object.values(xhr.responseJSON.errors)[0][0]; 

                        }

                    }

                    

                    messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-exclamation-circle"></i> ' + errorMsg + '</small>').slideDown();

                }

            });

        });

    }



    // 3. RESET PASSWORD FLOW (Iske andar reset-password.blade ka code aayega)

    if ($('#resetPasswordForm').length > 0) {

        

        $('#resetPasswordForm').on('submit', function(e) {

            e.preventDefault(); 

            

            let form = $(this);

            let submitBtn = $('#updateBtn');

            let messageBox = $('#updateMessage');



            // UI State: Submitting

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

            messageBox.slideUp().removeClass('text-danger text-success');



            $.ajax({

                url: form.attr('action'),

                type: 'POST',

                data: form.serialize(),

                success: function(response) {

                    // UI State: Success

                    submitBtn.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i> Updated');

                    messageBox.html('<small class="fw-bold text-success">' + response.message + '</small>').slideDown();

                    

                    // Redirect to login page

                    setTimeout(function() {

                        window.location.href = response.redirect;

                    }, 1500);

                },

                error: function(xhr) {

                    // UI State: Error

                    submitBtn.prop('disabled', false).html('Update Password');

                    let errorMsg = 'An error occurred. Please try again.';

                    

                    if (xhr.responseJSON) {

                        if (xhr.responseJSON.errors) {

                            // Extract the first specific validation error directly

                            errorMsg = Object.values(xhr.responseJSON.errors)[0][0];

                        } else if (xhr.responseJSON.message) {

                            // Fallback to general message (like session expired)

                            errorMsg = xhr.responseJSON.message;

                        }

                    }

                    

                    messageBox.html('<small class="fw-bold text-danger"><i class="fas fa-exclamation-circle"></i> ' + errorMsg + '</small>').slideDown();

                }

            });

        });

    }



});