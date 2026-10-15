<!DOCTYPE html>

<html lang="en">

<head>

    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <title>@yield('title', 'Admin Login') - {{ config('app.name', 'Admin') }}</title>

    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />

    <link rel="icon" href="{{ asset('admin/img/logo.svg') }}" type="image/x-icon" />

    

    <!-- Icons & Core CSS -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    

    <!-- Theme CSS -->


    <link rel="stylesheet" href="{{ asset('admin/css/style.css') }}" />



    <style>

        /* Custom CSS to perfectly center the login card */

        .auth-wrapper {

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            background-color: #f4f5f8;

        }

        .login-card {

            width: 100%;

            max-width: 400px;

            border-radius: 10px;

            box-shadow: 0 4px 20px 0 rgba(0,0,0,0.05);

        }

    </style>

</head>

<body>

    <div class="auth-wrapper">

        @yield('content')

    </div>



    <!-- Core JS -->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    

    <script>

        // 1. SWEETALERT TOAST NOTIFICATIONS

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



        // Backend Middleware se aane wale messages yahan show honge

        @if(session('success'))

            Toast.fire({ icon: 'success', title: "{{ session('success') }}" });

        @endif

        

        @if(session('error'))

            Toast.fire({ icon: 'error', title: "{{ session('error') }}" });

        @endif





        $(document).ready(function() {

            // GLOBAL EYE ICON TOGGLE FOR AUTH PAGES

            $(document).on('click', '.toggle-password, #togglePassword', function() {

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

        });

    </script>



    {{-- <script src="{{ asset('admin/js/auth.js') }}?v={{ time() }}"></script> --}}
    <script src="{{ asset('admin/js/auth.js') }}"></script>





    @stack('scripts')

</body>

</html>