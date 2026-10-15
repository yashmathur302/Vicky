$(document).ready(function() {
    
    // 1. USERS CREATE MODULE
    if ($('#createUserForm').length) {
        $('#createUserForm').on('submit', function(e) {
            e.preventDefault();

            let $btn = $('#saveUserBtn');
            let form = $(this);

            $.ajax({
                url: form.attr('action'), // Route automatically form ke action se mil jayega
                type: 'POST',
                data: form.serialize(),
                beforeSend: function() {
                    $btn.prop('disabled', true).text('Saving...');
                },
                success: function(response) {
                    if(response.status === 'success') {
                        window.location.href = response.redirect || window.location.href; 
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).text('Save');
                    if (xhr.status === 422) {
                        showToastMessage('warning', xhr.responseJSON.message); 
                    } else {
                        showToastMessage('error', "Something went wrong. Please try again.");
                    }
                }
            });
        });
    }

    // Yahan future me events ya categories ka create form add kar sakte hain
    // if ($('#createEventForm').length) { ... }
});