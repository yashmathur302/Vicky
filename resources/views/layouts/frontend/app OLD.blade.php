<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'TeachYou') - Frontend</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">



    <!-- Bootstrap 5 CSS -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom Frontend CSS -->

    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">

    

    @stack('styles')

</head>

<body class="bg-light">

    <!-- Sirf yield karein, naya design apna layout khud sambhalega -->
    @yield('content')

    <!-- jQuery & Bootstrap Bundle -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @php
        // Frontend web guard ka Remember Me state check
        $frontendGuard = Auth::guard('web');
        $recallerName = $frontendGuard->getRecallerName();
        $isAuthenticated = $frontendGuard->check();
        $isRemembered = $isAuthenticated && request()->hasCookie($recallerName);
    @endphp
    
    <!-- Global AJAX Setup for CSRF -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <!-- GLOBAL SWEETALERT TOAST -->
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });


        // GLOBAL TOAST HELPER
        function showToastMessage(type, message) {
            Toast.fire({
                icon: type,
                title: message
            });
        }


        // SESSION MESSAGE AFTER AUTO LOGOUT
        $(document).ready(function () {

            const logoutReason = sessionStorage.getItem(
                'frontend_logout_reason'
            );

            if (logoutReason === 'inactivity') {

                sessionStorage.removeItem(
                    'frontend_logout_reason'
                );

                Toast.fire({
                    icon: 'error',
                    title: 'You have been securely logged out due to inactivity.'
                });
            }
        });

        // GLOBAL DYNAMIC SWEETALERT DECISION CONFIRMATION
        function confirmGlobalDecision(options, onConfirm, onDeny, onCancel) {
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 not loaded');
                if (confirm(options.text || 'Are you sure?')) {
                    if (typeof onConfirm === 'function') onConfirm();
                } else {
                    if (typeof onDeny === 'function') onDeny();
                }
                return;
            }

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
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof onConfirm === 'function') onConfirm();
                } else if (result.isDenied) {
                    if (typeof onDeny === 'function') onDeny();
                } else if (result.isDismissed) {
                    if (typeof onCancel === 'function') onCancel();
                }
            });
        }
    </script>

    <!-- Frontend Auto Logout -->
    <script>
        const isAuthenticated = {{ $isAuthenticated ? 'true' : 'false' }};
        const isRemembered = {{ $isRemembered ? 'true' : 'false' }};

        let idleTime = 0;
        let isProcessing = false;
        let userIsActive = false;

        const maxIdleSeconds = 300; // 5 minutes

        const keepActiveUrl = '{{ route("keep.active") }}';
        const logoutUrl = '{{ route("logout") }}';

        // 1. FORM PROCESSING
        $(document).on('submit', 'form', function () {
            isProcessing = true;
        });

        // 2. AJAX START (Exclude Background Tasks)
        $(document).ajaxSend(function (event, jqXHR, ajaxOptions) {
            if (ajaxOptions.url !== keepActiveUrl && ajaxOptions.url !== logoutUrl) {
                isProcessing = true;
            }
        });

        // 3. AJAX COMPLETE (FIXED: Don't reset idle time on background tasks)
        $(document).ajaxComplete(function (event, jqXHR, ajaxOptions) {
            if (ajaxOptions.url !== keepActiveUrl && ajaxOptions.url !== logoutUrl) {
                isProcessing = false;
                idleTime = 0; // Ye sirf main user-initiated AJAX (jaise form submit) par hi 0 hoga
            }
        });

        // 4. USER ACTIVITY
        $(document).on('mousemove keydown scroll click touchstart', function () {
            idleTime = 0;
            userIsActive = true;
        });

        // 5. AUTO LOGOUT (Sirf Remember Me OFF hone par)
        if (isAuthenticated && !isRemembered) {
            setInterval(function () {
                if (!isProcessing) {
                    idleTime++;

                    if (idleTime >= maxIdleSeconds) {
                        isProcessing = true; // Lock further execution

                        // Reason store karo taaki redirect ke baad message show ho
                        sessionStorage.setItem('frontend_logout_reason', 'inactivity');

                        // Logout request
                        let form = document.createElement('form');
                        form.method = 'POST';
                        form.action = logoutUrl;

                        let token = document.createElement('input');
                        token.type = 'hidden';
                        token.name = '_token';
                        token.value = '{{ csrf_token() }}';

                        form.appendChild(token);
                        document.body.appendChild(form);

                        form.submit();
                    }
                }
            }, 1000);
        }

        // 6. HEARTBEAT (Sirf authenticated user ke liye)
        if (isAuthenticated) {
            setInterval(function () {
                // Agar user active tha, tabhi heartbeat bhejo
                if (userIsActive && !isProcessing) {
                    $.ajax({
                        url: keepActiveUrl,
                        type: 'GET',
                        cache: false,
                        global: true, 
                        error: function () {}
                    });
                    userIsActive = false; // Reset kardo next cycle ke liye
                }
            }, 20000); // 20 Seconds
        }
    </script>

    <!-- Frontend Auto Logout -->
    <!-- <script>
        const isAuthenticated = {{ $isAuthenticated ? 'true' : 'false' }};
        const isRemembered = {{ $isRemembered ? 'true' : 'false' }};

        let idleTime = 0;
        let isProcessing = false;
        let userIsActive = false;

        const maxIdleSeconds = 300;

        const keepActiveUrl = '{{ route("keep.active") }}';
        const logoutUrl = '{{ route("logout") }}';


        // FORM PROCESSING
        $(document).on('submit', 'form', function () {
            isProcessing = true;
        });


        // AJAX START
        $(document).ajaxSend(function (event, jqXHR, ajaxOptions) {

            if (
                ajaxOptions.url !== keepActiveUrl &&
                ajaxOptions.url !== logoutUrl
            ) {
                isProcessing = true;
            }
        });


        // AJAX COMPLETE
        $(document).ajaxComplete(function (event, jqXHR, ajaxOptions) {

            if (ajaxOptions.url !== logoutUrl) {
                isProcessing = false;
                idleTime = 0;
            }
        });


        // USER ACTIVITY
        $(document).on('mousemove keydown scroll click', function () {
            idleTime = 0;
            userIsActive = true;
        });


        /*
        * AUTO LOGOUT
        * Sirf Remember Me OFF hone par
        */
        if (isAuthenticated && !isRemembered) {

            setInterval(function () {

                if (!isProcessing) {

                    idleTime++;

                    if (idleTime >= maxIdleSeconds) {

                        isProcessing = true;

                        // Reason store karo taaki redirect ke baad message show ho
                        sessionStorage.setItem(
                            'frontend_logout_reason',
                            'inactivity'
                        );


                        // Logout request
                        let form = document.createElement('form');

                        form.method = 'POST';
                        form.action = logoutUrl;

                        let token = document.createElement('input');

                        token.type = 'hidden';
                        token.name = '_token';
                        token.value = '{{ csrf_token() }}';

                        form.appendChild(token);
                        document.body.appendChild(form);

                        form.submit();
                    }
                }

            }, 1000);
        }


        /*
        * HEARTBEAT
        * Sirf authenticated user ke liye
        */
        if (isAuthenticated) {

            setInterval(function () {

                if (userIsActive && !isProcessing) {

                    $.ajax({
                        url: keepActiveUrl,
                        type: 'GET',
                        cache: false,
                        error: function () {}
                    });

                    userIsActive = false;
                }

            }, 20000);
        }
    </script> -->

    
    <!-- Custom Frontend Auth JS -->
    <script src="{{ asset('frontend/js/auth.js') }}"></script>
    
    @stack('scripts')
</body>

</html>
