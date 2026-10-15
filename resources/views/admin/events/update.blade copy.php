@extends('layouts.admin.app')

@section('title', 'Update Event')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">

        <!-- Heading & Breadcrumbs -->
        <div class="d-flex align-items-center">
            <h3 class="fw-bold mb-3 me-3">Update Event</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ route('admin.events.list') }}">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
                <li class="separator"><i class="fas fa-angle-right"></i></li>
                <li class="nav-item"><a href="{{ route('admin.events.list') }}">Events</a></li>
                <li class="separator"><i class="fas fa-angle-right"></i></li>
                <li class="nav-item"><a href="#">Update Event</a></li>
            </ul>
        </div>

        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('admin.events.list') }}" class="btn btn-primary">
                <i class="fas fa-reply-all"></i> Back to list
            </a>
        </div>

    </div>

    <div class="card">
        <div class="card-body">
            <form id="updateEventForm" action="{{ route('admin.events.update', $event->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <!-- Column 1: Core Settings -->
                    <div class="col-md-4">
                        <div class="card card-round">
                            <div class="card-header">
                                <div class="card-title">Event Basic</div>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="user_id">Admin (Event Creator) <span class="text-danger">*</span></label>
                                    <select class="form-select form-control" id="user_id" name="user_id" required>
                                        <option value="">Select Admin</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ $event->user_id == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- <div class="form-group">
                                    <label for="user_id">Admin <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="user_id" name="user_id"
                                        value="{{ $event->creator->name }}" required>
                                </div> --}}

                                <div class="form-group">
                                    <label for="title">Event Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title"
                                        value="{{ $event->title }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="category_id">Category</label>
                                    <select class="form-select form-control" id="category_id" name="category_id">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ $event->category_id == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="privacy_level">Privacy Level <span class="text-danger">*</span></label>
                                    <select class="form-select form-control" id="privacy_level" name="privacy_level"
                                        required>
                                        <option value="public" {{ $event->privacy_level == 'public' ? 'selected' : '' }}>
                                            Public</option>
                                        <option value="friends" {{ $event->privacy_level == 'friends' ? 'selected' : '' }}>
                                            Friends Only</option>
                                        <option value="private" {{ $event->privacy_level == 'private' ? 'selected' : '' }}>
                                            Private</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="status">Event Status <span class="text-danger">*</span></label>
                                    <select class="form-select form-control" id="status" name="status" required>
                                        <option value="scheduled" {{ $event->status == 'scheduled' ? 'selected' : '' }}>
                                            Scheduled</option>
                                        <option value="ongoing" {{ $event->status == 'ongoing' ? 'selected' : '' }}>Ongoing
                                        </option>
                                        <option value="completed" {{ $event->status == 'completed' ? 'selected' : '' }}>
                                            Completed</option>
                                        <option value="cancelled" {{ $event->status == 'cancelled' ? 'selected' : '' }}>
                                            Cancelled</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="location_name">Location Name</label>
                                    <input type="text" class="form-control" id="location_name" name="location_name"
                                        value="{{ $event->location_name }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Event Details & Media -->
                    <div class="col-md-8">
                        <div class="card card-round">
                            <div class="card-header">
                                <div class="card-title">Event Details & Media</div>
                            </div>
                            <div class="card-body">

                                <!-- Cover Image -->
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="cover_image" class="form-label fw-bold mb-0">Event Cover Image:</label>
                                        @if(!empty($event->cover_image))
                                            <!-- GLOBAL IMAGE VIEWER BUTTON -->
                                            <button type="button" class="btn btn-sm btn-info view-image-btn"
                                                data-image="{{ asset('uploads/events/cover/' . $event->cover_image) }}">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        @endif
                                    </div>

                                    <input type="file" id="cover_image" name="cover_image" class="dropify" accept="image/*"
                                        @if(!empty($event->cover_image))
                                            data-default-file="{{ asset('uploads/events/cover/' . $event->cover_image) }}#.png"
                                        @endif />
                                    <input type="hidden" name="remove_cover_image" id="remove_cover_image" value="0">
                                </div>

                                {{-- <div class="form-group">
                                    @if(!empty($event->cover_image))
                                        <!-- AGAR IMAGE HAI: Button dikhao aur Input me data-default-file lagao -->
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label for="cover_image" class="form-label fw-bold mb-0">Event Cover Image:</label>
                                            <button type="button" class="btn btn-sm btn-info view-image-btn" data-image="{{ asset('uploads/events/cover/' . $event->cover_image) }}">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </div>
                                        <input type="file" id="cover_image" name="cover_image" class="dropify" accept="image/*" data-default-file="{{ asset('uploads/events/cover/' . $event->cover_image) }}#.png" />
                                    @else
                                        <!-- AGAR IMAGE NAHI HAI: Bina button ka label aur bina attribute ka normal Input dikhao -->
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label for="cover_image" class="form-label fw-bold mb-0">Event Cover Image:</label>
                                        </div>
                                        <input type="file" id="cover_image" name="cover_image" class="dropify" accept="image/*" />
                                    @endif
                                    
                                    <input type="hidden" name="remove_cover_image" id="remove_cover_image" value="0">
                                </div> --}}

                                <!-- START DATE & TIME -->
                                <div class="row mt-3">
                                    <div class="col-md-12"><label class="form-label fw-bold mb-1">Start Date & Time <span
                                                class="text-danger">*</span></label></div>
                                    <div class="col-md-6">
                                        <div class="form-group pt-0">
                                            <div class="input-group">
                                                <input type="text" class="form-control global-datepicker" name="start_date" placeholder="DD/MM/YYYY"
                                                    value="{{ $event->start_at ? $event->start_at->format('d/m/Y') : '' }}"
                                                    data-start-date="0d" autocomplete="off" required>
                                                <span class="input-group-text calendar-icon" style="cursor: pointer;">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group pt-0">
                                            <input type="time" class="form-control" name="start_time"
                                                value="{{ $event->start_at ? $event->start_at->format('H:i') : '' }}"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <!-- END DATE & TIME -->
                                <div class="row mt-2">
                                    <div class="col-md-12"><label class="form-label fw-bold mb-1">End Date & Time</label>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group pt-0">
                                            <div class="input-group">
                                                <input type="text" class="form-control global-datepicker" name="end_date"
                                                    placeholder="DD/MM/YYYY"
                                                    value="{{ $event->end_at ? $event->end_at->format('d/m/Y') : '' }}"
                                                    data-start-date="0d" autocomplete="off" required>
                                                <span class="input-group-text calendar-icon" style="cursor: pointer;">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group pt-0">
                                            <input type="time" class="form-control" name="end_time"
                                                value="{{ $event->end_at ? $event->end_at->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="description">Event Description</label>
                                    <textarea class="form-control" id="description" name="description"
                                        rows="5">{{ $event->description }}</textarea>
                                </div>
                            </div>

                            <div class="card-action text-end">
                                <button type="submit" class="btn btn-success" id="submitBtn">Update Event</button>
                                <a href="{{ route('admin.events.list') }}" class="btn btn-danger">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection


@push('script')
    <script src="{{ asset('admin/js/update.js') }}"></script>
@endpush