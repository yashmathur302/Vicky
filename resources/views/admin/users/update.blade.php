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

            <li class="nav-item"><a href="#">Update Profile</a></li>

        </ul>

    </div>

    <div class="mb-3">

        <a href="{{ route('admin.users.list') }}" class="btn btn-primary">

            <i class="fas fa-reply-all"></i> Back to list

        </a>

    </div>

</div>

<form id="updateUserForm" action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">

    @csrf

    

    <!-- TOP SECTION: IMAGES & KEY STATS -->

    <div class="card mb-4 shadow-sm border-0">

        <div class="card-body p-4">

            <div class="row align-items-center">

                

                <!-- Left: Profile Image -->

                <div class="col-md-4">

                    <div class="form-group p-0">

                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <label for="profile_picture" class="form-label fw-bold mb-0">Profile Image</label>

                            @if($user->userInfo?->profile_pic_url && $user->userInfo?->profile_pic_url !== asset('uploads/users/profile/default.jpg'))

                                <button type="button" class="btn btn-sm btn-info view-image-btn py-0 px-2" data-image="{{ $user->userInfo->profile_pic_url }}?v={{ time() }}">

                                    <i class="fas fa-eye"></i>

                                </button>

                            @endif

                        </div>

                        <input type="file" id="profile_picture" name="profile_picture" class="dropify" accept="image/*" data-allowed-file-extensions="png jpg jpeg webp" data-height="150"

                            @if($user->userInfo?->profile_pic_url && $user->userInfo?->profile_pic_url !== asset('uploads/users/profile/default.jpg')) 

                                data-default-file="{{ $user->userInfo->profile_pic_url }}?v={{ time() }}#.png" 

                            @endif

                        />

                        <div id="custom_error_profile_picture" class="text-danger small fw-bold mt-1 d-none"></div>

                        <input type="hidden" name="remove_profile_picture" id="remove_profile_picture" value="0">



                        {{-- Profile Upload Progress Bar --}}

                        <div class="upload-progress-container d-none mt-2">

                            <div class="progress" style="height: 20px; border-radius: 5px; background-color: #e9ecef;">

                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success upload-progress-bar fw-bold" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>

                            </div>

                            <div class="text-center small fw-bold text-muted mt-1 upload-status-text">Starting upload...</div>

                        </div>

                    </div>

                </div>



                <!-- Center: Stats & Info -->

                <div class="col-md-4 text-center">

                    <h4 class="fw-bold mb-1">{{ $user->name }}</h4>

                    <p class="text-muted small mb-3">{{ '@' . $user->username }}</p>

                    

                    <div class="row text-start justify-content-center px-3">

                        <div class="col-12 mb-2 d-flex justify-content-between border-bottom pb-1">

                            <span class="text-muted"><i class="fas fa-id-badge me-2"></i>User ID:</span>

                            <span class="fw-bold">#{{ $user->id }}</span>

                        </div>

                        <div class="col-12 mb-2 d-flex justify-content-between border-bottom pb-1">

                            <span class="text-muted"><i class="fas fa-calendar-alt me-2"></i>Joined:</span>

                            <span class="fw-bold">{{ $user->created_at->format('d M Y') }}</span>

                        </div>

                        <div class="col-12 mb-2 d-flex justify-content-between border-bottom pb-1">

                            <span class="text-muted"><i class="fas fa-clock me-2"></i>Last Login:</span>

                            <span class="fw-bold text-primary" id="last-login-text">{{ $user->last_login ? $user->last_login->format('d M Y, h:i A') : 'N/A' }}</span>

                        </div>

                        <div class="col-12 mb-2 d-flex justify-content-between border-bottom pb-1">

                            <span class="text-muted"><i class="fas fa-user-friends me-2"></i>Friends:</span>

                            <span class="fw-bold badge bg-secondary">0</span>

                        </div>

                        <div class="col-12 d-flex justify-content-between">

                            <span class="text-muted"><i class="fas fa-camera-retro me-2"></i>Posts:</span>

                            <span class="fw-bold badge bg-secondary">0</span>

                        </div>

                    </div>

                </div>



                <!-- Right: Cover Image -->

                <div class="col-md-4">

                    <div class="form-group p-0">

                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <label for="cover_image" class="form-label fw-bold mb-0">Cover Image</label>

                            @if(!empty($user->userInfo?->cover_pic_url))

                                <button type="button" class="btn btn-sm btn-info view-image-btn py-0 px-2" data-image="{{ $user->userInfo->cover_pic_url }}?v={{ time() }}">

                                    <i class="fas fa-eye"></i>

                                </button>

                            @endif

                        </div>

                        <input type="file" id="cover_image" name="cover_image" class="dropify" accept="image/*" data-allowed-file-extensions="png jpg jpeg webp" data-height="150"

                            @if(!empty($user->userInfo?->cover_pic_url)) 

                                data-default-file="{{ $user->userInfo->cover_pic_url }}?v={{ time() }}#.png" 

                            @endif

                        />

                        <div id="custom_error_cover_image" class="text-danger small fw-bold mt-1 d-none"></div>

                        <input type="hidden" name="remove_cover_image" id="remove_cover_image" value="0">



                        {{-- Cover Upload Progress Bar --}}

                        <div class="upload-progress-container d-none mt-2">

                            <div class="progress" style="height: 20px; border-radius: 5px; background-color: #e9ecef;">

                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success upload-progress-bar fw-bold" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>

                            </div>

                            <div class="text-center small fw-bold text-muted mt-1 upload-status-text">Starting upload...</div>

                        </div>

                    </div>

                </div>



            </div>

        </div>

    </div>



    <!-- BOTTOM SECTION: TABS -->

    <div class="card card-round shadow-sm">

        <div class="card-header p-0 border-bottom-0">

            <ul class="nav nav-tabs nav-line nav-color-primary w-100" id="userTabs" role="tablist">

                <li class="nav-item w-25 text-center">

                    <a class="nav-link active fw-bold" id="account-tab" data-bs-toggle="tab" href="#account" role="tab" aria-selected="true"><i class="fas fa-cog me-2"></i> Account</a>

                </li>

                <li class="nav-item w-25 text-center">

                    <a class="nav-link fw-bold" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab" aria-selected="false"><i class="fas fa-user-edit me-2"></i> Profile</a>

                </li>

                <li class="nav-item w-25 text-center">

                    <a class="nav-link fw-bold" id="security-tab" data-bs-toggle="tab" href="#security" role="tab" aria-selected="false"><i class="fas fa-shield-alt me-2"></i> Security</a>

                </li>

                <li class="nav-item w-25 text-center">

                    <a class="nav-link fw-bold" id="privacy-tab" data-bs-toggle="tab" href="#privacy" role="tab" aria-selected="false"><i class="fas fa-lock me-2"></i> Privacy</a>

                </li>

            </ul>

        </div>

        

        <div class="card-body p-4">

            <div class="tab-content" id="userTabsContent">

                

                <!-- ================= 1. ACCOUNT TAB ================= -->

                <div class="tab-pane fade show active" id="account" role="tabpanel" aria-labelledby="account-tab">

                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group pt-0">

                                <label for="username">Username <span class="text-danger">*</span></label>

                                <input type="text" class="form-control" id="username" name="username" value="{{ $user->username }}" required>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group pt-0">

                                <label for="email">Email Address <span class="text-danger">*</span></label>

                                <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">

                                <label for="password">New Password</label>

                                <div class="input-group">

                                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current">

                                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1"><i class="fas fa-eye"></i></button>

                                </div>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">

                                <label for="password_confirmation">Confirm Password</label>

                                <div class="input-group">

                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password">

                                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1"><i class="fas fa-eye"></i></button>

                                </div>

                            </div>

                        </div>

                        <div class="col-md-3 my-4 d-flex">

                            <!-- Toggle: Verified Email -->

                            <div class="form-check form-switch form-check-reverse p-0">

                                <label class="form-check-label fw-bold" for="email_verified_at">Email Verified</label>

                                <input class="form-check-input custom-toggle ms-3 m-0" type="checkbox" id="email_verified_at" name="email_verified_at" value="1" {{ $user->email_verified_at ? 'checked' : '' }}>

                            </div>

                        </div>

                        <div class="col-md-3 my-4 d-flex">

                            <!-- Toggle: Banned User -->

                            <div class="form-check form-switch form-check-reverse p-0">

                                {{-- <label class="form-check-label fw-bold" for="is_banned">Ban User</label>

                                <input class="form-check-input custom-toggle ms-3 m-0" type="checkbox" id="is_banned" name="is_banned" value="1" {{ ($user->is_banned ?? '') == 'banned' ? 'checked' : '' }}> --}}

                                <label class="form-check-label fw-bold text-danger" for="is_banned">Ban User</label>

                                <input class="form-check-input custom-toggle ms-3 m-0" type="checkbox" id="is_banned" name="is_banned" value="1" {{ $user->is_banned ? 'checked' : '' }}>

                            </div>

                        </div>



                        <div class="col-md-6 my-4 d-flex">
                            <!-- Read Only Status Field -->
                            <div class="fw-bold me-3">Account Status</div>
                            <div id="live-account-status">
                                @if($user->account_status == 'active')
                                    <span class="badge bg-success px-3 py-2" style="font-size: 13px;">ACTIVE</span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2" style="font-size: 13px;">INACTIVE</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>



                <!-- ================= 2. PROFILE TAB ================= -->

                <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">

                    <div class="row">

                        <div class="col-md-4">

                            <div class="form-group pt-0">

                                <label for="name">Full Name <span class="text-danger">*</span></label>

                                <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="form-group pt-0">

                                <label for="dob">Date of Birth</label>

                                <div class="input-group">

                                    {{-- <input type="text" class="form-control global-datepicker" id="dob" name="dob" placeholder="DD/MM/YYYY" 

                                        value="{{ !empty($user->userInfo?->dob) ? \Carbon\Carbon::parse($user->userInfo->dob)->format('d/m/Y') : '' }}" 

                                        data-start-date="01/01/1950" autocomplete="off">

                                    <span class="input-group-text calendar-icon"><i class="fas fa-calendar-alt"></i></span> --}}



                                    <input type="text" class="form-control global-datepicker" id="dob" name="dob" placeholder="DD/MM/YYYY" 

                                                value="{{ !empty($user->userInfo?->dob) ? \Carbon\Carbon::parse($user->userInfo->dob)->format('d/m/Y') : '' }}" 

                                                data-start-date="01/01/1950" 

                                                data-end-date="-12y" 

                                                autocomplete="off">

                                            <span class="input-group-text calendar-icon" style="cursor: pointer;">

                                                <i class="fas fa-calendar-alt"></i>

                                            </span>

                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="form-group pt-0">

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



                        <div class="col-md-4">

                            <div class="form-group">

                                <label for="work">Work</label>

                                <input type="text" class="form-control" id="work" name="work" value="{{ $user->userInfo->work ?? '' }}">

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="form-group">

                                <label for="location">Location</label>

                                <input type="text" class="form-control" id="location" name="location" value="{{ $user->userInfo->location ?? '' }}">

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="form-group">

                                <label for="website">Website URL</label>

                                <input type="url" class="form-control" id="website" name="website" value="{{ $user->userInfo->website ?? '' }}">

                            </div>

                        </div>



                        <div class="col-md-12">

                            <div class="form-group">

                                <label for="bio">Bio</label>

                                <textarea class="form-control" id="bio" name="bio" rows="2">{{ $user->userInfo->bio ?? '' }}</textarea>

                            </div>

                        </div>



                        <!-- Social Links -->

                        <div class="col-md-12 mt-2">

                            <h6 class="fw-bold border-bottom pb-2 mb-3">Social Links</h6>

                            <div class="row">

                                <div class="col-md-3">

                                    <div class="input-group mb-3">

                                        <span class="input-group-text bg-light text-primary"><i class="fab fa-facebook-f"></i></span>

                                        <input type="url" class="form-control" name="facebook_link" value="{{ $user->userInfo->facebook_link ?? '' }}" placeholder="Facebook URL">

                                    </div>

                                </div>

                                <div class="col-md-3">

                                    <div class="input-group mb-3">

                                        <span class="input-group-text bg-light text-danger"><i class="fab fa-instagram"></i></span>

                                        <input type="url" class="form-control" name="instagram_link" value="{{ $user->userInfo->instagram_link ?? '' }}" placeholder="Instagram URL">

                                    </div>

                                </div>

                                <div class="col-md-3">

                                    <div class="input-group mb-3">

                                        <span class="input-group-text bg-light text-info"><i class="fab fa-linkedin-in"></i></span>

                                        <input type="url" class="form-control" name="linkedin_link" value="{{ $user->userInfo->linkedin_link ?? '' }}" placeholder="LinkedIn URL">

                                    </div>

                                </div>

                                <div class="col-md-3">

                                    <div class="input-group mb-3">

                                        <span class="input-group-text bg-light text-dark"><i class="fab fa-twitter"></i></span>

                                        <input type="url" class="form-control" name="twitter_link" value="{{ $user->userInfo->twitter_link ?? '' }}" placeholder="Twitter URL">

                                    </div>

                                </div>

                            </div>

                        </div>



                        <!-- Education & Interests -->

                        <div class="col-md-6">

                            <div class="form-group pt-0">

                                <label for="education">Education (Comma separated)</label>

                                <input type="text" class="form-control" id="education" name="education" value="{{ is_array($user->userInfo->education ?? null) ? implode(', ', $user->userInfo->education) : '' }}">

                                <div id="Education" class="form-text text-muted mt-1">Press enter or comma before mentioning next education.</div>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group pt-0">

                                <label for="interests">Interests (Comma separated)</label>

                                <input type="text" class="form-control" id="interests" name="interests" value="{{ is_array($user->userInfo->interests ?? null) ? implode(', ', $user->userInfo->interests) : '' }}">

                                <div id="Intersets" class="form-text text-muted mt-1">Press enter or comma before mentioning next interest.</div>

                            </div>

                        </div>



                        <!-- Status & Badges -->

                        <div class="col-md-12 mt-2">
                            <div class="d-flex gap-4 align-items-center">
                                {{-- <div class="form-check form-switch form-check-reverse d-flex justify-content-between align-items-center p-0 mt-2">
                                    <label class="form-check-label fw-bold" for="is_private">Private Account</label>
                                    <input class="form-check-input custom-toggle ms-3 m-0" type="checkbox" id="is_private" name="is_private" value="1" {{ ($user->userInfo->is_private ?? false) ? 'checked' : '' }}>
                                </div> --}}

                                {{-- <div class="form-check form-switch form-check-reverse d-flex justify-content-between align-items-center p-0 mt-2">
                                    <label class="form-check-label fw-bold" for="is_verified">Verified</label>
                                    <input class="form-check-input custom-toggle ms-3 m-0" type="checkbox" id="is_verified" name="is_verified" value="1" {{ ($user->userInfo->is_verified ?? false) ? 'checked' : '' }}>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 3. SECURITY TAB ================= -->
                <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                    <h5 class="fw-bold mb-3">Login History</h5>
                    <div id="login-history-table-container" class="ajax-pagination-container position-relative">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">S.No</th>
                                        <th>Browser</th>
                                        <th>Operating System</th>
                                        <th>Date & Time</th>
                                        <th>IP Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loginHistories as $index => $history)
                                        <tr>
                                            <td>{{ $loginHistories->firstItem() + $index }}</td>
                                            <td>
                                                @if(stripos($history->browser, 'chrome') !== false) <i class="fab fa-chrome text-primary me-2"></i>
                                                @elseif(stripos($history->browser, 'safari') !== false) <i class="fab fa-safari text-primary me-2"></i>
                                                @elseif(stripos($history->browser, 'firefox') !== false) <i class="fab fa-firefox text-warning me-2"></i>
                                                @elseif(stripos($history->browser, 'edge') !== false) <i class="fab fa-edge text-info me-2"></i>
                                                @else <i class="fas fa-globe text-secondary me-2"></i> @endif
                                                {{ $history->browser }}
                                            </td>
                                            <td>
                                                @if(stripos($history->operating_system, 'windows') !== false) <i class="fab fa-windows text-info me-2"></i>
                                                @elseif(stripos($history->operating_system, 'mac') !== false) <i class="fab fa-apple text-dark me-2"></i>
                                                @elseif(stripos($history->operating_system, 'android') !== false) <i class="fab fa-android text-success me-2"></i>
                                                @else <i class="fas fa-laptop text-secondary me-2"></i> @endif
                                                {{ $history->operating_system }}
                                            </td>
                                            <td>{{ $history->created_at->format('d M Y, h:i A') }}</td>
                                            <td><span class="badge bg-secondary">{{ $history->ip_address }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fas fa-info-circle me-1"></i> No login history found for this user.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>



                        <div class="d-flex justify-content-end mt-3">

                            {{ $loginHistories->links('pagination::bootstrap-5') }}

                        </div>

                    </div>

                </div>



                <!-- ================= 4. PRIVACY TAB ================= -->

                <div class="tab-pane fade" id="privacy" role="tabpanel" aria-labelledby="privacy-tab">

                    <div class="text-center py-5 text-muted">

                        <i class="fas fa-user-lock fa-3x mb-3 text-light"></i>

                        <h5>Privacy Settings</h5>

                        <p>Privacy controls will be implemented here in future updates.</p>

                    </div>

                </div>



            </div>

        </div>



        <div class="card-footer text-end bg-light p-3">

            <button type="submit" class="btn btn-success fw-bold px-4" id="submitBtn">Save Changes</button>

            <a href="{{ route('admin.users.list') }}" class="btn btn-danger fw-bold px-4">Cancel</a>

        </div>

    </div>

</form>



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