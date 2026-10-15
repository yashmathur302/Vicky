@extends('layouts.admin.app')



@section('title', 'Users')



@section('content')

<div class="page-header">

    <h3 class="fw-bold mb-3">Users</h3>

    <ul class="breadcrumbs mb-3">

        <li class="nav-home">

            <a href="{{ route('admin.users.list') }}">

                <i class="fas fa-home"></i>

            </a>

        </li>

        <li class="separator"><i class="fas fa-angle-right"></i></li>

        <li class="nav-item"><a href="#">Users</a></li>

        <li class="separator"><i class="fas fa-angle-right"></i></li>

        <li class="nav-item"><a href="#">Users List</a></li>

    </ul>

</div>



<div class="row">

    <div class="col-md-12">

        <div class="card card-round">

            <div class="card-header">

                <div class="d-flex align-items-center">

                    <h4 class="card-title">All Users</h4>

                    <a href="javascript:void(0);" class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#createUserModal">

                        <i class="fa fa-plus"></i> Add New User

                    </a>

                </div>

            </div>

            

            <div class="card-body">



                <!-- FILTERS SECTION -->

                <div class="row g-3 mb-4" id="users-filters-container" data-url="{{ route('admin.users.list') }}">

                    <div class="col">

                        <input type="text" id="search_name" class="form-control filter-input" placeholder="Search by Name...">

                    </div>

                    <!-- <div class="col">

                        <input type="text" id="search_username" class="form-control filter-input" placeholder="Search by Username...">

                    </div> -->

                </div>



                <div class="table-responsive">

                    <table class="table table-hover table-striped">

                        <thead class="thead-light">

                            <tr>

                                <th>S.no</th>

                                {{-- <th>Profile</th> --}}

                                <th>Name</th>

                                <th>Username</th>

                                <th>Email</th>

                                {{-- <th>Status</th> --}}

                                <th class="text-center">Action</th>

                            </tr>

                        </thead>

                        <!-- TBODY ko ek ID di hai taaki AJAX se ise replace kiya ja sake -->

                        <tbody id="user-table-body">

                            @forelse($users as $key => $user)

                                <tr id="user-row-{{ $user->id }}">

                                    <td>{{ $users->firstItem() + $key }}</td>

                                    <td>

                                        <div class="fw-bold">{{ $user->name }}</div>

                                    </td>

                                    <td>

                                        <div>{{ $user->username }}</div>

                                    </td>

                                    <td>{{ $user->email }}</td>

                                    <td class="text-center">

                                        <div class="form-button-action">

                                            <a href="{{ route('admin.users.update.index', $user->id) }}" data-bs-toggle="tooltip" title="Edit User" class="btn btn-link">

                                                <i class="fa fa-edit"></i>

                                            </a>

                                            <a href="javascript:void(0);" onclick="confirmGlobalDelete('{{ route('admin.users.delete', $user->id) }}')" data-bs-toggle="tooltip" title="Delete User" class="btn btn-link">

                                                <i class="fa fa-trash text-danger"></i>

                                            </a>

                                            {{-- <a href="javascript:void(0);" onclick="deleteUser({{ $user->id }})" data-bs-toggle="tooltip" title="Delete User" class="btn btn-link">

                                                <i class="fa fa-trash text-danger"></i>

                                            </a> --}}

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6" class="text-center py-4 text-muted">No users found.</td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>



                <!-- PAGINATION ko ek ID di hai -->

                <div class="mt-4" id="pagination-container">

                    {{ $users->links('pagination::bootstrap-5') }}

                </div>

            </div>

        </div>

    </div>

</div>



<!-- create popup included -->

@include('admin.users.create')



@endsection



@push('script')

    <script src="{{ asset('admin/js/list.js') }}"></script>

@endpush