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
                                            <!-- Check if userInfo exists, has a URL, and is NOT the default image -->
                                            @if($user->userInfo?->profile_pic_url && $user->userInfo?->profile_pic_url !== asset('uploads/users/profile/default.jpg'))
                                                <button type="button" class="btn btn-sm btn-info view-image-btn" data-image="{{ $user->userInfo->profile_pic_url }}">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            @endif
                                        </div>
                                        
                                        <!-- Notice #.png at the end to trick Dropify into previewing WebP -->
                                        <input type="file" id="profile_picture" name="profile_picture" class="dropify" accept="image/*" data-allowed-file-extensions="png jpg jpeg webp"
                                            @if($user->userInfo?->profile_pic_url && $user->userInfo?->profile_pic_url !== asset('uploads/users/profile/default.jpg')) 
                                                data-default-file="{{ $user->userInfo->profile_pic_url }}#.png" 
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
                                            <!-- Simply check if the cover pic url is not empty (null-safe handles missing userInfo) -->
                                            @if(!empty($user->userInfo?->cover_pic_url))
                                                <button type="button" class="btn btn-sm btn-info view-image-btn" data-image="{{ $user->userInfo->cover_pic_url }}">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            @endif
                                        </div>
                                        
                                        <input type="file" id="cover_image" name="cover_image" class="dropify" accept="image/*" data-allowed-file-extensions="png jpg jpeg webp"
                                            @if(!empty($user->userInfo?->cover_pic_url)) 
                                                data-default-file="{{ $user->userInfo->cover_pic_url }}#.png" 
                                            @endif
                                        />
                                        <input type="hidden" name="remove_cover_image" id="remove_cover_image" value="0">
                                    </div>
                                </div>
                            </div>

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
                                            <!-- EXACT REPLACEMENT FOR DOB INPUT IN update.blade.php -->
                                            <input type="text" class="form-control global-datepicker" id="dob" name="dob" placeholder="DD/MM/YYYY" 
                                                value="{{ !empty($user->userInfo?->dob) ? \Carbon\Carbon::parse($user->userInfo->dob)->format('d/m/Y') : '' }}" 
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