<!-- Modal Container aur Form dono create.blade.php me rahenge -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="createUserForm" action="{{ route('admin.users.create')}}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="name">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter full name" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="username">Username <span class="text-danger">*</span></label>
                        {{-- <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" autocomplete="off" required> --}}
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="email">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="password">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" autocomplete="new-password" required>
                            <!-- Eye Icon Toggle -->
                            <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="passwordHelp" class="form-text text-muted mt-1">
                            Password must be at least 8 characters long, start with a capital letter, and include at least one lowercase letter and one special character.
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" autocomplete="new-password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveUserBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
    <script src="{{ asset('admin/js/create.js') }}"></script>
@endpush