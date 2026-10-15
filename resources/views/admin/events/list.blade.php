@extends('layouts.admin.app')



@section('title', 'Events List')



@section('content')

<div class="page-header">

    <h3 class="fw-bold mb-3">Events</h3>

    <ul class="breadcrumbs mb-3">

        <li class="nav-home">

            <a href="{{ route('admin.dashboard') }}">

                <i class="fas fa-home"></i>

            </a>

        </li>

        <li class="separator"><i class="fas fa-angle-right"></i></li>

        <li class="nav-item"><a href="#">Events</a></li>

        <li class="separator"><i class="fas fa-angle-right"></i></li>

        <li class="nav-item"><a href="#">Events List</a></li>

    </ul>

</div>



<div class="row">

    <div class="col-md-12">

        <div class="card card-round">

            <div class="card-header">

                <div class="d-flex align-items-center">

                    <h4 class="card-title">All Events</h4>

                </div>

            </div>

            

            <div class="card-body">


                <!-- FILTERS SECTION -->

                <div class="row g-3 mb-4" id="event-filters-container" data-url="{{ route('admin.events.list') }}">

                    <div class="col-md-4">

                        <input type="text" id="search_title" class="form-control filter-input" placeholder="Search by Event Title...">

                    </div>

                    <div class="col-md-4">

                        <select id="filter_category" class="form-select form-control filter-input">

                            <option value="">All Categories</option>

                            @foreach($categories as $category)

                                <option value="{{ $category->id }}">{{ $category->name }}</option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-4">

                        <select id="filter_status" class="form-select form-control filter-input">

                            <option value="">All Status</option>

                            <option value="scheduled">Scheduled</option>

                            <option value="ongoing">Ongoing</option>

                            <option value="completed">Completed</option>

                            <option value="cancelled">Cancelled</option>

                        </select>

                    </div>

                </div>



                <div class="table-responsive">

                    <table class="table table-hover table-striped">

                        <thead class="thead-light">

                            <tr>

                                <th>S.no</th>

                                <th>Event Title</th>

                                <th>Category</th>

                                <th>Admin</th>

                                <th>Date & Time</th>

                                {{-- <th>Privacy</th> --}}

                                <th>Status</th>

                                <th class="text-center">Action</th>

                            </tr>

                        </thead>

                        <tbody id="event-table-body">

                            @forelse($events as $key => $event)

                                <tr id="event-row-{{ $event->id }}">

                                    <td>{{ $events->firstItem() + $key }}</td>

                                    <td>

                                        <div class="fw-bold">{{ $event->title }}</div>

                                        {{-- <small class="text-muted"><i class="fas fa-tag"></i> {{ $event->category->name ?? 'NA' }}</small> --}}

                                    </td>

                                    <td>

                                        <div><i class="fas fa-tag me-1"></i> {{ $event->category->name ?? 'NA' }}</div>

                                    </td>

                                    <td>{{ $event->creator->name ?? 'NA' }}</td>

                                    <td>

                                        <div><strong>Start:</strong> {{ $event->start_at->format('d M Y, h:i A') }}</div>

                                        @if($event->end_at)

                                            <div class="text-muted" style="font-size: 12px;"><strong>End:</strong> {{ $event->end_at->format('d M Y, h:i A') }}</div>

                                        @endif

                                    </td>

                                    <td>

                                        @if($event->status == 'scheduled')

                                            <span class="badge text-bg-info text-white">Scheduled</span>

                                        @elseif($event->status == 'ongoing')

                                            <span class="badge text-bg-primary">Ongoing</span>

                                        @elseif($event->status == 'completed')

                                            <span class="badge text-bg-success">Completed</span>

                                        @else

                                            <span class="badge text-bg-danger">Cancelled</span>

                                        @endif

                                    </td>   

                                    <td class="text-center">

                                        <div class="form-button-action">

                                            <a href="{{ route('admin.events.update.index', $event->id) }}" data-bs-toggle="tooltip" title="Edit Event" class="btn btn-link">

                                                <i class="fa fa-edit"></i>

                                            </a>

                                            <!-- Shortcode: Global Delete JS called here -->

                                            <a href="javascript:void(0);" onclick="confirmGlobalDelete('{{ route('admin.events.delete', $event->id) }}')" data-bs-toggle="tooltip" title="Delete Event" class="btn btn-link">

                                                <i class="fa fa-trash text-danger"></i>

                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="7" class="text-center py-4 text-muted">No events found.</td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>



                <div class="mt-4" id="pagination-container">

                    {{ $events->links('pagination::bootstrap-5') }}

                </div>

            </div>

        </div>

    </div>

</div>

@endsection



@push('script')

    <script src="{{ asset('admin/js/list.js') }}"></script>

@endpush



{{-- @push('script')

<script>

    $(document).ready(function() {

        let typingTimer;                

        let doneTypingInterval = 300;  



        // Trigger AJAX on both input (typing) and change (dropdown selection)

        $('.filter-input').on('keyup change', function () {

            clearTimeout(typingTimer);

            typingTimer = setTimeout(fetchEvents, doneTypingInterval);

        });



        function fetchEvents() {

            let search_title = $('#search_title').val();

            let category_id = $('#filter_category').val();

            let status = $('#filter_status').val();



            $.ajax({

                url: "{{ route('admin.events.list') }}",

                type: "GET",

                data: {

                    search_title: search_title,

                    category_id: category_id,

                    status: status

                },

                success: function(response) {

                    let newBody = $(response).find('#event-table-body').html();

                    let newPagination = $(response).find('#pagination-container').html();

                    

                    $('#event-table-body').html(newBody);

                    $('#pagination-container').html(newPagination);

                },

                error: function() {

                    console.error("Failed to load filtered events.");

                }

            });

        }

    });

</script>

@endpush --}}