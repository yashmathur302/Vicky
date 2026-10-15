(function () {
    'use strict';

    // jQuery AJAX ke har request me CSRF token
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfMeta ? csrfMeta.content : '' }
    });

    // Global toast
    var Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        didOpen: function (toast) {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });

    window.Toast = Toast;

    window.showToastMessage = function (type, message) {
        Toast.fire({ icon: type, title: message });
    };

    // Global decision dialog (confirm / deny / cancel)
    window.confirmGlobalDecision = function (options, onConfirm, onDeny, onCancel) {
        Swal.fire({
            title: options.title || 'Are you sure?',
            text: options.text || '',
            icon: options.icon || 'question',
            showDenyButton: options.showDeny !== false,
            showCancelButton: options.showCancel !== false,
            confirmButtonText: options.confirmText || 'Yes',
            denyButtonText: options.denyText || 'No',
            cancelButtonText: options.cancelText || 'Cancel',
            confirmButtonColor: options.confirmColor || '#d33',
            denyButtonColor: options.denyColor || '#3085d6',
            cancelButtonColor: options.cancelColor || '#6c757d',
            reverseButtons: true,
            allowOutsideClick: false
        }).then(function (result) {
            if (result.isConfirmed && typeof onConfirm === 'function') { onConfirm(); }
            else if (result.isDenied && typeof onDeny === 'function') { onDeny(); }
            else if (result.isDismissed && typeof onCancel === 'function') { onCancel(); }
        });
    };

    // Auto-logout ke baad login page par message
    try {
        if (sessionStorage.getItem('frontend_logout_reason') === 'inactivity') {
            sessionStorage.removeItem('frontend_logout_reason');
            window.showToastMessage('error', 'You have been securely logged out due to inactivity.');
        }
    } catch (e) { /* storage blocked */ }
})();