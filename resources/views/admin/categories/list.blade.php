@extends('layouts.admin.app')



@section('title', 'Categories')



@section('content')

    <div class="page-header">

        <h3 class="fw-bold mb-3">Categories</h3>

        <ul class="breadcrumbs mb-3">

            <li class="nav-home">

                <a href="{{ route('admin.categories.list') }}">

                    <i class="fas fa-home"></i>

                </a>

            </li>

            <li class="separator"><i class="fas fa-angle-right"></i></li>

            <li class="nav-item"><a href="#">Categories</a></li>

            <li class="separator"><i class="fas fa-angle-right"></i></li>

            <li class="nav-item"><a href="#">Categories List</a></li>

        </ul>

    </div>



    <div class="row">

        <div class="col-md-12">

            <div class="card card-round">

                <div class="card-header">

                    <div class="d-flex align-items-center">

                        <h4 class="card-title">All Categories</h4>

                        <a href="javascript:void(0);" class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal"

                            data-bs-target="#createCategoryModal">

                            <i class="fa fa-plus"></i> Add New Category

                        </a>

                    </div>

                </div>



                <div class="card-body">



                    <!-- Filters -->

                    <div class="row g-3 mb-4" id="categories-filters-container"

                        data-url="{{ route('admin.categories.list') }}">

                        <div class="col">

                            <input type="text" id="search_name" class="form-control filter-input"

                                placeholder="Search category by name...">

                        </div>

                    </div>



                    <!-- Table -->

                    <div class="table-responsive">

                        <table class="table table-hover table-striped">

                            <thead class="thead-light">

                                <tr>

                                    <th>S.no</th>

                                    <th>Category Name</th>

                                    {{-- <th>Slug</th> --}}

                                    <th>Status</th>

                                    <th class="text-center">Action</th>

                                </tr>

                            </thead>

                            <tbody id="category-table-body">

                                @forelse($categories as $key => $category)

                                    <tr>

                                        <td>{{ $categories->firstItem() + $key }}</td>

                                        <td>

                                            <div class="fw-bold">{{ $category->name }}</div>

                                        </td>

                                        {{-- <td><span class="text-muted">{{ $category->slug }}</span></td> --}}

                                        <td>

                                            @if($category->status == 'active')

                                                <span class="badge text-bg-success">Active</span>

                                            @else

                                                <span class="badge text-bg-danger">Inactive</span>

                                            @endif

                                        </td>

                                        <td class="text-center">

                                            <div class="form-button-action">

                                                <a href="javascript:void(0);" data-bs-toggle="tooltip" title="Edit Category"

                                                    class="btn btn-link edit-category-btn" data-id="{{ $category->id }}"

                                                    data-name="{{ $category->name }}" data-status="{{ $category->status }}"

                                                    data-action="{{ route('admin.categories.update', $category->id) }}">

                                                    <i class="fa fa-edit"></i>

                                                </a>

                                                <!-- Delete Button -->

                                                <a href="javascript:void(0);"

                                                    onclick="confirmGlobalDelete('{{ route('admin.categories.delete', $category->id) }}')"

                                                    data-bs-toggle="tooltip" title="Delete Category" class="btn btn-link">

                                                    <i class="fa fa-trash text-danger"></i>

                                                </a>

                                            </div>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="5" class="text-center py-4 text-muted">No categories found.</td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>



                    <div class="mt-4" id="pagination-container">

                        {{ $categories->links('pagination::bootstrap-5') }}

                    </div>

                </div>

            </div>

        </div>

    </div>



    {{-- CREATE CATEGORY MODAL --}}

    <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel"

        aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold" id="createCategoryModalLabel">Add New Category</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>



                <form id="createCategoryForm" action="{{ route('admin.categories.create') }}" method="POST"

                    autocomplete="off">

                    @csrf

                    <div class="modal-body">

                        <div class="form-group mb-3">

                            <label for="name">Category Name <span class="text-danger">*</span></label>

                            <input type="text" class="form-control" id="name" name="name"

                                placeholder="E.g. Technology, Music, Sports" required>

                        </div>



                        <div class="form-group mb-3">

                            <label for="status">Status <span class="text-danger">*</span></label>

                            <select class="form-select form-control" id="status" name="status" required>

                                <option value="active" selected>Active</option>

                                <option value="inactive">Inactive</option>

                            </select>

                        </div>

                    </div>



                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                        <button type="submit" class="btn btn-primary" id="saveCategoryBtn">Save</button>

                    </div>

                </form>

            </div>

        </div>

    </div>



    {{-- UPDATE CATEGORY MODAL --}}

    <div class="modal fade" id="updateCategoryModal" tabindex="-1" aria-labelledby="updateCategoryModalLabel"

        aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold" id="updateCategoryModalLabel">Edit Category</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>



                <!-- Action URL JavaScript se dynamically set hoga -->

                <form id="updateCategoryForm" method="POST" autocomplete="off">

                    @csrf

                    <div class="modal-body">

                        <div class="form-group mb-3">

                            <label for="update_name">Category Name <span class="text-danger">*</span></label>

                            <input type="text" class="form-control" id="update_name" name="name" required>

                        </div>



                        <div class="form-group mb-3">

                            <label for="update_status">Status <span class="text-danger">*</span></label>

                            <select class="form-select form-control" id="update_status" name="status" required>

                                <option value="active">Active</option>

                                <option value="inactive">Inactive</option>

                            </select>

                        </div>

                    </div>



                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                        <button type="submit" class="btn btn-primary" id="updateCategoryBtn">Update</button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection





@push('script')

    <script src="{{ asset('admin/js/list.js') }}"></script>

@endpush