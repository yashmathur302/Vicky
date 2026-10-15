$(document).ready(function () {
    let countdownInterval = null;

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

    // Global Form Submission Logic for Login & Register
    $('#frontendLoginForm, #frontendRegisterForm').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let submitBtn = form.find('button[type="submit"]');
        let messageBox = form.find('.form-message'); // Message container div
        let formData = form.serialize();

        // UI State: Submitting
        let originalBtnText = submitBtn.text();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        messageBox.slideUp().removeClass('text-danger text-success text-warning');

        if (countdownInterval) clearInterval(countdownInterval);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function (response) {
                submitBtn.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i> Success');
                messageBox.html('<div class="alert alert-success p-2 small fw-bold mb-0">' + response.message + '</div>').slideDown();
                
                setTimeout(function () {
                    window.location.href = response.redirect;
                }, 1000);
            },
            error: function (xhr) {
                // 1. CSRF Token Mismatch (419)
                if (xhr.status === 419) {
                    submitBtn.prop('disabled', true).html('<i class="fas fa-sync-alt fa-spin"></i> Refreshing...');
                    messageBox.html('<div class="alert alert-warning p-2 small fw-bold mb-0"><i class="fas fa-shield-alt"></i> Security token expired. Refreshing page...</div>').slideDown();
                    setTimeout(() => window.location.reload(), 1500);
                    return;
                }

                // 2. Rate Limit (429)
                if (xhr.status === 429) {
                    let secondsLeft = xhr.responseJSON.locked_seconds;
                    submitBtn.prop('disabled', true).html('<i class="fas fa-lock"></i> Locked');
                    messageBox.html('<div class="alert alert-danger p-2 small fw-bold mb-0"><i class="fas fa-ban"></i> Too many attempts. Try again in <span id="timerText">' + secondsLeft + '</span>s.</div>').slideDown();

                    countdownInterval = setInterval(function () {
                        secondsLeft--;
                        $('#timerText').text(secondsLeft);
                        if (secondsLeft <= 0) {
                            clearInterval(countdownInterval);
                            submitBtn.prop('disabled', false).html(originalBtnText);
                            messageBox.slideUp();
                        }
                    }, 1000);
                    return;
                }

                // 3. Validation or Auth Errors (422 / 401)
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
});