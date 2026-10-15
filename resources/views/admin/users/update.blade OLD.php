@extends('layouts.admin.app')

@section('title', 'Update User')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center"> 
    <div class="d-flex align-items-center">
        <h3 class="fw-bold mb-3">Update User</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="{{ route('admin.users.list') }}">
                    <i class="fas fa-home"></i>
                </a>
            </li>
            <li class="separator"><i class="fas fa-angle-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.users.list') }}">Users</a></li>
            <li class="separator"><i class="fas fa-angle-right"></i></li>
            <li class="nav-item"><a href="#">Update User</a></li>
        </ul>
    </div>
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('admin.users.list') }}" class="btn btn-primary">
            <i class="fas fa-reply-all"></i> Back to list
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- AJAX ke liye id="updateUserForm" add kiya gaya hai -->
        <form id="updateUserForm" action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <!-- Column 1: Account & Security -->
                <div class="col-md-4">
                    <div class="card card-round">
                        <div class="card-header">
                            <div class="card-title">Account Details</div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                            </div>

                            <div class="form-group">
                                <label for="username">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" value="{{ $user->username }}" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div id="passwordHelp" class="form-text text-muted mt-1">
                                    Password must be at least 8 characters long, start with a capital letter, and include at least one lowercase letter and one special character.
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Leave blank to keep current password">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-round mt-3">
                        <div class="card-header">
                            <div class="card-title">Account Status & Flags</div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="account_status">Account Status</label>
                                <select class="form-select form-control" id="account_status" name="account_status">
                                    @php $status = $user->userInfo->account_status ?? 'active'; @endphp
                                    <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="banned" {{ $status == 'banned' ? 'selected' : '' }}>Banned</option>
                                </select>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="is_private" name="is_private" {{ ($user->userInfo->is_private ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_private">Private Account</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="is_verified" name="is_verified" {{ ($user->userInfo->is_verified ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_verified">Verified Badge</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Profile Details (User Info) -->
                <div class="col-md-8">
                    <div class="card card-round">
                        <div class="card-header">
                            <div class="card-title">Profile Information</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Profile Picture -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label for="profile_picture" class="form-label fw-bold mb-0">Profile Picture:</label>
                                            @if(!empty($user->userInfo->profile_picture))
                                                <button type="button" class="btn btn-sm btn-info view-image-btn" data-image="{{ asset('uploads/users/profile/' . $user->userInfo->profile_picture) }}">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            @endif
                                        </div>
                                        
                                        <!-- Notice #.png at the end to trick Dropify into previewing WebP -->
                                        <input type="file" id="profile_picture" name="profile_picture" class="dropify" accept="image/*"
                                            @if(!empty($user->userInfo->profile_picture)) 
                                                data-default-file="{{ asset('uploads/users/profile/' . $user->userInfo->profile_picture) }}#.png" 
                                            @endif
                                        />
                                        <input type="hidden" name="remove_profile_picture" id="remove_profile_picture" value="0">
                                    </div>
                                </div>
                                
                                <!-- Cover Image -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label for="cover_image" class="form-label fw-bold mb-0">Cover Image:</label>
                                            @if(!empty($user->userInfo->cover_image))
                                                <button type="button" class="btn btn-sm btn-info view-image-btn" data-image="{{ asset('uploads/users/cover/' . $user->userInfo->cover_image) }}">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            @endif
                                        </div>
                                        
                                        <input type="file" id="cover_image" name="cover_image" class="dropify" accept="image/*"
                                            @if(!empty($user->userInfo->cover_image)) 
                                                data-default-file="{{ asset('uploads/users/cover/' . $user->userInfo->cover_image) }}#.png" 
                                            @endif
                                        />
                                        <input type="hidden" name="remove_cover_image" id="remove_cover_image" value="0">
                                    </div>
                                </div>
                            </div>


                            {{-- <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="profile_picture" class="form-label fw-bold">Profile Picture:</label>
                                        
                                        <input type="file" id="profile_picture" name="profile_picture" class="dropify" accept="image/*"
                                            @if(!empty($user->userInfo->profile_picture)) 
                                                data-default-file="{{ asset('uploads/users/profile/' . $user->userInfo->profile_picture) }}" 
                                            @endif
                                        />
                                        <input type="hidden" name="remove_profile_picture" id="remove_profile_picture" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cover_image" class="form-label fw-bold">Cover Image:</label>
                                        
                                        <input type="file" id="cover_image" name="cover_image" class="dropify" accept="image/*"
                                            @if(!empty($user->userInfo->cover_image)) 
                                                data-default-file="{{ asset('uploads/users/cover/' . $user->userInfo->cover_image) }}" 
                                            @endif
                                        />
                                        <input type="hidden" name="remove_cover_image" id="remove_cover_image" value="0">
                                    </div>
                                </div>
                            </div> --}}

                            <div class="form-group">
                                <label for="bio">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3">{{ $user->userInfo->bio ?? '' }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gender">Gender</label>
                                        <select class="form-select form-control" id="gender" name="gender">
                                            @php $gender = $user->userInfo->gender ?? ''; @endphp
                                            <option value="">Select Gender</option>
                                            <option value="male" {{ $gender == 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ $gender == 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ $gender == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dob">Date of Birth</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control global-datepicker" id="dob" name="dob" placeholder="DD/MM/YYYY" 
                                                value="{{ optional($user->userInfo->dob ?? '')->format('d/m/Y') }}" 
                                                data-start-date="01/01/1950" 
                                                data-end-date="-31/12/2021" 
                                                autocomplete="off">
                                            <span class="input-group-text calendar-icon" style="cursor: pointer;">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="location">Location</label>
                                        <input type="text" class="form-control" id="location" name="location" value="{{ $user->userInfo->location ?? '' }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="work">Work</label>
                                        <input type="text" class="form-control" id="work" name="work" value="{{ $user->userInfo->work ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="website">Website URL</label>
                                <input type="url" class="form-control" id="website" name="website" value="{{ $user->userInfo->website ?? '' }}">
                            </div>

                            <!-- JSON Casted Array Fields (Imploded to show as comma separated strings) -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="education">Education (Comma separated)</label>
                                        <input type="text" class="form-control" id="education" name="education" value="{{ implode(', ', $user->userInfo->education ?? []) }}">
                                        <div id="education" class="form-text text-muted mt-1">
                                            Press enter or comma before mentioning next education detail.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="interests">Interests (Comma separated)</label>
                                        <input type="text" class="form-control" id="interests" name="interests" value="{{ implode(', ', $user->userInfo->interests ?? []) }}">
                                        <div id="Intersets" class="form-text text-muted mt-1">
                                            Press enter or comma before mentioning next interest.
                                        </div>
                                    </div>
                                </div>
                                

                                <!-- Social Links Section -->
                                <div class="col-md-12 mt-3">
                                    <h5>Social Links</h5>
                                    <div class="row">
                                        <!-- Facebook -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="facebook_link">Facebook URL</label>
                                                <input type="url" class="form-control" id="facebook_link" name="facebook_link" 
                                                    value="{{ old('facebook_link', $user->userInfo->facebook_link ?? '') }}" 
                                                    placeholder="https://facebook.com/username">
                                            </div>
                                        </div>

                                        <!-- Instagram -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="instagram_link">Instagram URL</label>
                                                <input type="url" class="form-control" id="instagram_link" name="instagram_link" 
                                                    value="{{ old('instagram_link', $user->userInfo->instagram_link ?? '') }}"
                                                    placeholder="https://instagram.com/username">
                                            </div>
                                        </div>

                                        <!-- LinkedIn -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="linkedin_link">LinkedIn URL</label>
                                                <input type="url" class="form-control" id="linkedin_link" name="linkedin_link" 
                                                    value="{{ old('linkedin_link', $user->userInfo->linkedin_link ?? '') }}"
                                                    placeholder="https://linkedin.com/in/username">
                                            </div>
                                        </div>

                                        <!-- Twitter / X -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="twitter_link">Twitter URL</label>
                                                <input type="url" class="form-control" id="twitter_link" name="twitter_link" 
                                                    value="{{ old('twitter_link', $user->userInfo->twitter_link ?? '') }}"
                                                    placeholder="https://twitter.com/username">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="card-action text-end">
                            <button type="submit" class="btn btn-success" id="submitBtn">Update</button>
                            <a href="{{ route('admin.users.list') }}" class="btn btn-danger">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Image Viewer Modal -->
<div class="modal fade" id="imageViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Image Viewer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img src="" id="viewerImage" class="img-fluid rounded shadow-sm" alt="View Image" style="max-height: 70vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

@endsection

@push('script')
    <script src="{{ asset('admin/js/update.js') }}"></script>
@endpush

{{-- <script>
    $(document).ready(function() {

        // AJAX Form Submission
        $('#updateUserForm').on('submit', function(e) {
            e.preventDefault(); // Prevent standard HTML submission
            
            let form = $(this);
            let submitBtn = $('#submitBtn');
            let formData = new FormData(this);

            submitBtn.prop('disabled', true).text('Updating...');

            $.ajax({
                url: form.attr('action'),
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    // Success Message
                    // alert(response.message); // Aap ise SweetAlert se replace kar sakte hain
                    
                    // Redirect to list page
                    if(response.redirect) {
                        window.location.href = response.redirect;
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).text('Update User');
                    
                    // Error Message
                    if(xhr.status === 422) {
                        let res = xhr.responseJSON;
                        alert(res.message); // Validation error
                    } else {
                        alert('Something went wrong. Please try again.');
                    }
                }
            });
        });
    
        Image logics
        if ($.fn.dropify) {
            $('.dropify').dropify({
                error: {
                    'fileSize': 'The file size is too big (4M max).',
                    'imageFormat': 'The image format is not allowed (webp, png, jpg, jpeg).'
                }
            }).on('dropify.afterClear', function(event, element) {
                const inputName = $(element.element).attr('name');
                if (inputName === 'profile_picture') {
                    $('#remove_profile_picture').val(1);
                } else if (inputName === 'cover_image') {
                    $('#remove_cover_image').val(1);
                }
            });
        }

        // Image Viewer Logic
        $('.view-image-btn').on('click', function(e) {
            e.preventDefault();
            // Button ke data-image attribute se URL uthao
            let imageUrl = $(this).data('image'); 

            // Modal ke <img> tag me source set karo
            $('#viewerImage').attr('src', imageUrl); 
            
            // Modal open karo (Bootstrap 5 standard)
            var myModal = new bootstrap.Modal(document.getElementById('imageViewerModal'));
            myModal.show();
        });

        Eye Icon Toggle Logic
        $('.toggle-password').on('click', function() {
            // Jisme click kiya, usi input group ke input ko dhoondho
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

        // Bootstrap Datepicker Initialization on INPUT field
        var dateInput = $('#dob');
        dateInput.datepicker({
            format: 'dd/mm/yyyy', // DD/MM/YYYY format
            endDate: '0d',
            startDate: '-70y',
            autoclose: true,
            todayHighlight: true,
            clearBtn: true
        });

        // Calendar Icon Toggle Logic
        var isPickerOpen = false;
        dateInput.on('show', function() { isPickerOpen = true; });
        dateInput.on('hide', function() { isPickerOpen = false; });

        $('#calendar-icon').on('click', function() {
            if (isPickerOpen) {
                dateInput.datepicker('hide');
            } else {
                dateInput.datepicker('show');
            }
        });


        // 1. Initialize Tagify and store their instances in variables
        // var inputInterests = document.querySelector('#interests');
        // var tagifyInterests = new Tagify(inputInterests, {
        //     originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(','),
        //     addTagOnBlur: false // Bahar click karne par auto-tag banne se rokega
        // });

        // var inputEducation = document.querySelector('#education');
        // var tagifyEducation = new Tagify(inputEducation, {
        //     originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(','),
        //     addTagOnBlur: false // Bahar click karne par auto-tag banne se rokega
        // });
            
        //     // Validation: Check karein agar user ne Interests me kuch type kiya par tag nahi banaya
        // if (tagifyInterests.DOM.input.textContent.trim() !== '') {
        //     alert('Interests field error: Please press Comma (,) or Enter to confirm your tag before updating.');
        //     tagifyInterests.DOM.input.focus(); // Wapas usi input par focus le jayega
        //     return false; // Form submit rok dega
        // }

        // // Validation: Check karein agar user ne Education me kuch type kiya par tag nahi banaya
        // if (tagifyEducation.DOM.input.textContent.trim() !== '') {
        //     alert('Education field error: Please press Comma (,) or Enter to confirm your tag before updating.');
        //     tagifyEducation.DOM.input.focus(); // Wapas usi input par focus le jayega
        //     return false; // Form submit rok dega
        // }
       
    });
</script> --}}