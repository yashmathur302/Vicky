<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Admin') }}</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="{{ asset('admin/img/logo.svg') }}" type="image/x-icon" />

    <!-- ICONS (Replaced local fonts.min.css with CDNs) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('admin/css/style.css') }}" />
    <!-- Dynamic Page Styles -->
    @stack('style')
  </head>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
      @include('layouts.admin.partials.sidebar')
      <div class="main-panel">
        <!-- Header / Topbar -->
        @include('layouts.admin.partials.header')
        <!-- Main Content -->
        <div class="container">
          <div class="page-inner">
            @yield('content')
          </div>
        </div>
        <!-- Footer -->
        @include('layouts.admin.partials.footer')
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- jQuery Scrollbar -->
    <script src="{{ asset('admin/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
    <script src="{{ asset('admin/js/main.js') }}"></script>

    {{-- GLOBAL MODALS (Available on all pages) --}}
    <!-- 1. Global Image Viewer Modal -->
    <div class="modal fade" id="globalImageViewerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Image Viewer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <img src="" id="globalViewerImage" class="img-fluid rounded shadow-sm" alt="View Image" style="max-height: 70vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    {{-- GLOBAL ADMIN SCRIPTS --}}
    @php
        // CHECK REMEMBER ME STATE:
        // Laravel "Remember Me" ke liye ek specific recaller cookie banata hai.
        // Agar ye cookie request me hai, matlab user ne 'Remember Me' check kiya tha.
        $recallerName = Auth::guard()->getRecallerName();
        $isRemembered = Auth::check() && request()->hasCookie($recallerName);
    @endphp
   <script>
        // 1. SWEETALERT TOAST NOTIFICATIONS (Replaces Bootstrap Alerts)
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        // Laravel Session Messages ko Toast me convert karna
        @if(session('success'))
            Toast.fire({ icon: 'success', title: "{{ session('success') }}" });
        @endif
        @if(session('error'))
            Toast.fire({ icon: 'error', title: "{{ session('error') }}" });
        @endif

        // Global AJAX Error Handler (For Toastr/SweetAlert in AJAX calls)
        function showToastMessage(type, message) {
            Toast.fire({ icon: type, title: message });
        }

        $(document).ready(function() {
            
            if ($.fn.dropify) {
                $('.dropify').dropify({
                    error: {
                        'fileSize': 'The file size is too big (2M max).',
                        'imageFormat': 'The image format is not allowed (webp, png, jpg, jpeg).'
                    }
                }).on('dropify.afterClear', function(event, element) {
                    let inputName = $(element.element).attr('name');
                    $('#remove_' + inputName).val(1); // Updates #remove_cover_image
                });
            }

            // GLOBAL MODAL RESET (Enhanced with all Edge Cases)
            $(document).on('hidden.bs.modal', '.modal', function () {
                let modal = $(this);
                let form = modal.find('form');
                
                if(form.length) { 
                    form[0].reset(); // Standard text fields, checkboxes, radios clear karega
                }

                // 1. Submit Button ko un-freeze aur text reset karega
                modal.find('button[type="submit"]').prop('disabled', false).each(function() {
                    let originalText = $(this).data('original-text') || 'Save';
                    $(this).html(originalText); 
                });

                // 2. Dropify Images ko clear karega
                let dropifyElements = modal.find('.dropify');
                if (dropifyElements.length > 0) {
                    modal.find('.dropify-clear').click(); 
                }

                // 3. Custom Error Messages & Progress bar hide karega
                modal.find('.text-danger').addClass('d-none').text('');
                modal.find('.upload-progress-container').addClass('d-none');
                modal.find('.upload-progress-bar').css('width', '0%').removeClass('bg-info').addClass('bg-success');

                // 4. PASSWORD VISIBILITY RESET (Eye Icon & Input Type)
                modal.find('.toggle-password, .password-toggle').each(function() {
                    let input = $(this).closest('.input-group').find('input');
                    let icon = $(this).find('i');
                    
                    if(input.length) {
                        input.attr('type', 'password'); // Wapas bullet dots (password) banayega
                        icon.removeClass('fa-eye-slash').addClass('fa-eye'); // Icon ko wapas close eye karega
                    }
                });

                // 5. Select2 / Third-Party Dropdown Reset (Agar future me use karein)
                modal.find('select').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).val(null).trigger('change'); 
                    }
                });

                // 6. Tagify Inputs Reset (Agar modal ke andar Tagify use hua ho)
                modal.find('input, textarea').each(function() {
                    if (this.__tagify) {
                        this.__tagify.removeAllTags();
                    }
                });
            });

            // GLOBAL EYE ICON TOGGLE
            $(document).on('click', '.toggle-password', function() {
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

            // GLOBAL IMAGE VIEWER
            $(document).on('click', '.view-image-btn', function(e) {
                e.preventDefault();
                let imageUrl = $(this).data('image'); 
                $('#globalViewerImage').attr('src', imageUrl); 
                var myModal = new bootstrap.Modal(document.getElementById('globalImageViewerModal'));
                myModal.show();
            });

            // GLOBAL SMART DATEPICKER
            $('.global-datepicker').each(function() {
                let element = $(this);
                let options = {
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    todayHighlight: true,
                    clearBtn: true,
                    forceParse: false // NAYA: Calendar ko galat date guess karne aur force change karne se rokega
                };

                // Data attributes se exact start aur end date set karna
                if (element.attr('data-start-date')) { options.startDate = element.attr('data-start-date'); }
                if (element.attr('data-end-date')) { options.endDate = element.attr('data-end-date'); }

                element.datepicker(options);

                element.closest('.input-group').find('.calendar-icon').on('click', function() {
                    element.datepicker('show');
                });

                // ==========================================
                // ENHANCED SMART AUTO-FORMATTER
                // ==========================================
                function formatSmartDate(val) {
                    let pureNumbers = val.replace(/\D/g, ''); 
                    if (!pureNumbers) return null;

                    let day, month, year;
                    let isValid = false;

                    if (pureNumbers.length === 8) {
                        day = pureNumbers.slice(0, 2); month = pureNumbers.slice(2, 4); year = pureNumbers.slice(4, 8);
                        isValid = true;
                    } 
                    else if (pureNumbers.length === 7) {
                        year = pureNumbers.slice(3, 7);
                        let rem = pureNumbers.slice(0, 3);
                        let m2 = parseInt(rem.slice(1, 3)); 
                        let d2 = parseInt(rem.slice(0, 2)); 
                        if (m2 > 12 || m2 === 0) { day = rem.slice(0, 2); month = '0' + rem.slice(2, 3); } 
                        else if (d2 > 31 || d2 === 0) { day = '0' + rem.slice(0, 1); month = rem.slice(1, 3); } 
                        else { day = rem.slice(0, 2); month = '0' + rem.slice(2, 3); }
                        isValid = true;
                    } 
                    else if (pureNumbers.length === 6) {
                        let possibleYear = parseInt(pureNumbers.slice(2, 6));
                        if (possibleYear >= 1900 && possibleYear <= 2100) {
                            day = '0' + pureNumbers.slice(0, 1); month = '0' + pureNumbers.slice(1, 2); year = pureNumbers.slice(2, 6);
                            isValid = true;
                        } else { 
                            day = pureNumbers.slice(0, 2); month = pureNumbers.slice(2, 4);
                            let shortYear = parseInt(pureNumbers.slice(4, 6));
                            year = (shortYear > 30 ? "19" : "20") + pureNumbers.slice(4, 6);
                            isValid = true;
                        }
                    }

                    if (isValid && parseInt(day) > 0 && parseInt(day) <= 31 && parseInt(month) > 0 && parseInt(month) <= 12) {
                        return day + '/' + month + '/' + year;
                    }
                    return null;
                }

                // Handling Blur (Click outside), Enter Key, and Real-time Typing
                element.on('blur keydown keyup', function(e) {
                    let val = $(this).val();
                    
                    // Ignore agar value khali hai ya pehle se correct format (slashes) me hai
                    if (!val || val.includes('/')) return; 

                    // 1. Enter key dabane par
                    if (e.type === 'keydown' && e.which === 13) {
                        e.preventDefault(); 
                        let formatted = formatSmartDate(val);
                        if (formatted) {
                            $(this).val(formatted);
                            $(this).datepicker('update', formatted); // Calendar sync karega
                            $(this).datepicker('hide'); // Popup close kar dega
                        }
                    }
                    // 2. Bahar kanhi click karne par (Blur)
                    else if (e.type === 'blur') {
                        let formatted = formatSmartDate(val);
                        if (formatted) {
                            $(this).val(formatted);
                            $(this).datepicker('update', formatted);
                        }
                    }
                    // 3. Type karte waqt live sync (Jaise hi exact 8 digit dalenge)
                    else if (e.type === 'keyup') {
                        let pureNumbers = val.replace(/\D/g, '');
                        // Sirf 8 digits poore hone par real-time jump karega
                        if (pureNumbers.length === 8) {
                            let formatted = formatSmartDate(val);
                            if (formatted) {
                                $(this).val(formatted);
                                $(this).datepicker('update', formatted);
                            }
                        }
                    }
                });
            });

            // GLOBAL SMART TIMEPICKER
            $('.custom-timepicker').each(function() {
                let element = $(this);
                
                let fp = flatpickr(element, {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true,
                    allowInput: true, // User ko manually type karne dega
                    disableMobile: "true" // Mobile par native loop UI aane se rokega
                });

                // Agar custom clock icon par click ho, toh popup open karein
                element.closest('.input-group').find('.clock-icon').on('click', function() {
                    fp.open();
                });
            });

            // GLOBAL AJAX ERROR HANDLER

            $.ajaxSetup({

                statusCode: {

                    401: function(xhr) {

                        // Agar server 401 Unauthorized bhejta hai (session expired / logged out)

                        let response = JSON.parse(xhr.responseText);

                        Toast.fire({ icon: 'error', title: response.message || 'Session expired. Redirecting...' });

                        

                        // 2 second baad login page par redirect kar dega

                        setTimeout(function() {

                            window.location.reload(); 

                        }, 2000);

                    }

                }

            });

        });

        $(document).ready(function() {
            // Global AJAX Pagination Logic
            // Ye document par delegated event hai, isliye naye load hue pagination buttons par bhi kaam karega
            $(document).on('click', '.ajax-pagination-container .pagination a', function(e) {
                e.preventDefault(); // Page reload hone se rokega
                let url = $(this).attr('href'); // Next page ka URL
                let container = $(this).closest('.ajax-pagination-container');
                let containerId = container.attr('id'); // e.g., 'login-history-table-container'
                container.css('opacity', '0.5');
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        let newHtml = $(response).find('#' + containerId).html();
                        container.html(newHtml);
                        container.css('opacity', '1');
                    },
                    error: function() {
                        container.css('opacity', '1');
                        alert('Something went wrong while loading data.');
                    }
                });
            });
        });

        // GLOBAL SECURE DELETE CONFIRMATION FUNCTION
        function confirmGlobalDelete(url, customText = "This action cannot be undone!") {
            Swal.fire({
                title: 'Are you sure?',
                text: customText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Dynamically create a form for DELETE request
                    let form = document.createElement('form');
                    form.action = url;
                    form.method = 'POST';
                    // Inject CSRF Token directly using Blade (No meta tag required)
                    let csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    form.appendChild(csrfInput);
                    let methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Auto Logout logic
        let isRemembered = {{ $isRemembered ? 'true' : 'false' }};
        let idleTime = 0;
        let isProcessing = false; 
        let userIsActive = false; 
        
        // idle time set 
        const maxIdleSeconds = 300;

        $(document).on('submit', 'form', function() {
            isProcessing = true;
        });

        $(document).ajaxSend(function(event, jqXHR, ajaxOptions) {
            if (ajaxOptions.url !== '{{ route("admin.keep.active") }}' && ajaxOptions.url !== '{{ route("admin.logout") }}') {
                isProcessing = true;
            }
        });

        $(document).ajaxComplete(function(event, jqXHR, ajaxOptions) {
            if (ajaxOptions.url !== '{{ route("admin.logout") }}') {
                isProcessing = false; 
                idleTime = 0; 
            }
        });

        $(document).on('mousemove keydown scroll click', function() {
            idleTime = 0;
            userIsActive = true; 
        });

        // 2. The Auto-Logout Logic
        // YE SIRF TABHI CHALEGA JAB USER NE REMEMBER ME CHECK *NAHI* KIYA HO
        if (!isRemembered) {
            setInterval(function() {
                if (!isProcessing) {
                    idleTime++;
                    
                    if (idleTime >= maxIdleSeconds) {
                        isProcessing = true;
                        
                        if (typeof Toast !== 'undefined') {
                            Toast.fire({ icon: 'error', title: 'You have been securely logged out due to inactivity.' });
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'You have been securely logged out.', showConfirmButton: false, timer: 2000 });
                        }
                        
                        setTimeout(function() {
                            let form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '{{ route("admin.logout") }}';
                            
                            let token = document.createElement('input');
                            token.type = 'hidden';
                            token.name = '_token';
                            token.value = '{{ csrf_token() }}';
                            
                            form.appendChild(token);
                            document.body.appendChild(form);
                            form.submit();
                        }, 2000);
                    }
                }
            }, 1000);
        }

        // 3. Heartbeat Ping
        // Ye hamesha chalega taaki background form processing me CSRF tokens expire na hon
        setInterval(function() {
            if (userIsActive && !isProcessing) {
                $.ajax({
                    url: '{{ route("admin.keep.active") }}',
                    type: 'GET',
                    cache: false,
                    error: function() {} 
                });
                userIsActive = false;
            }
        }, 20000);
    </script>

    @stack('script')
  </body>

</html>

