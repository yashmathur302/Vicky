@extends('layouts.admin.app')

@section('title', 'Banned Users')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center"> 
    <div class="d-flex align-items-center">
        <h3 class="fw-bold mb-3">Banned Users</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-home"></i>
                </a>
            </li>
            <li class="separator"><i class="fas fa-angle-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.users.banned_user') }}">Banned Users List</a></li>
            {{-- <li class="separator"><i class="fas fa-angle-right"></i></li> --}}
            {{-- <li class="nav-item"><a href="#">Banned Users</a></li> --}}
        </ul>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-round">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title">All Banned Users</h4>
                </div>
            </div>
            
            <div class="card-body">

                <!-- FILTERS SECTION (Unique ID & Correct URL) -->
                <div class="row g-3 mb-4" id="banned-users-filters-container" data-url="{{ route('admin.users.banned_user') }}">
                    <div class="col">
                        <input type="text" id="search_banned_name" class="form-control filter-input" placeholder="Search Banned User by Name...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">S.no</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                {{-- <th class="text-center" width="10%">Action</th> --}}
                            </tr>
                        </thead>
                        <!-- TBODY UNIQUE ID -->
                        <tbody id="banned-user-table-body">
                            @forelse($users as $key => $user)
                                <tr>
                                    <td>{{ $users->firstItem() + $key }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $user->name }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $user->username }}</div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    {{-- <td class="text-center">
                                        <div class="form-button-action">
                                            <a href="{{ route('admin.users.update.index', $user->id) }}" data-bs-toggle="tooltip" title="Edit User" class="btn btn-link">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" onclick="confirmGlobalDelete('{{ route('admin.users.delete', $user->id) }}')" data-bs-toggle="tooltip" title="Delete User" class="btn btn-link">
                                                <i class="fa fa-trash text-danger"></i>
                                            </a>
                                        </div>
                                    </td> --}}
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-ban me-2 text-danger"></i> No banned users found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4" id="pagination-container">
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script src="{{ asset('admin/js/list.js') }}"></script>
@endpush