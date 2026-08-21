@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'User Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center flex-wrap gap-2">
            <h3 class="card-title fw-semibold mb-0"><i class="ti ti-users text-primary me-2"></i>Users</h3>
            @can('users.create')
                <button class="btn btn-primary btn-sm ms-auto" id="createUser">Add User</button>
            @endcan
        </div>

        <div class="card-body border-bottom">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-1">Role</label>
                    <select class="form-select form-select-sm" id="filterRole">
                        <option value="">All Roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Status</label>
                    <select class="form-select form-select-sm" id="filterStatus">
                        <option value="">All Status</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">School</label>
                    <select class="form-select form-select-sm" id="filterSchool">
                        <option value="">All Schools</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Created From</label>
                    <input type="date" class="form-control form-control-sm" id="filterCreatedFrom">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Created To</label>
                    <input type="date" class="form-control form-control-sm" id="filterCreatedTo">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button class="btn btn-sm btn-outline-secondary" id="applyFilters" title="Apply Filters"><i class="ti ti-filter"></i></button>
                    <button class="btn btn-sm btn-outline-danger" id="clearFilters" title="Clear Filters"><i class="ti ti-refresh"></i></button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-striped table-bordered w-100" id="usersTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>School</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th width="200">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    {{-- Create/Edit User Modal --}}
    <x-erp.side-panel
        id="userOffcanvas"
        formId="userForm"
        title="Add User"
        action="{{ route('admin.users.store') }}"
        method="POST"
        width="800px"
        saveButtonText="Save User"
        :multipart="true"
    >
        <input type="hidden" name="_method" value="POST" id="userMethod">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">User Details</h6>
        
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Name</label>
                <input class="form-control" name="name" required maxlength="255">
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Email</label>
                <input class="form-control" type="email" name="email" required maxlength="255">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium text-dark">Phone</label>
                <input class="form-control" name="phone" maxlength="30">
            </div>
            <div class="col-md-4">
                <label class="form-label required fw-medium text-dark" id="passwordLabel">Password</label>
                <input class="form-control" type="password" name="password" id="userPassword">
            </div>
            <div class="col-md-4">
                <label class="form-label required fw-medium text-dark" id="passwordConfirmLabel">Confirm Password</label>
                <input class="form-control" type="password" name="password_confirmation" id="userPasswordConfirmation">
            </div>
            <div class="col-md-4">
                <label class="form-label required fw-medium text-dark">Role</label>
                <select class="form-select" name="role" required id="userRole">
                    <option value="">Select Role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium text-dark">School</label>
                <select class="form-select" name="school_id" id="userSchool">
                    <option value="">Select School</option>
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label required fw-medium text-dark">Status</label>
                <select class="form-select" name="status" required id="userStatus">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($value === 'active')>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label fw-medium text-dark">Profile Photo</label>
                <input class="form-control" type="file" name="avatar" accept="image/png,image/jpeg,image/webp" id="userAvatar">
                <div class="mt-2" id="avatarPreview" style="display:none;">
                    <img src="" alt="Preview" class="rounded" style="max-height:80px;">
                </div>
            </div>
        </div>
    </x-erp.side-panel>

    {{-- Reset Password Modal --}}
    <x-erp.side-panel
        id="resetPasswordOffcanvas"
        formId="resetPasswordForm"
        title="Reset Password"
        action=""
        method="POST"
        width="400px"
        saveButtonText="Reset Password"
    >
        <input type="hidden" name="_method" value="PUT">
        <p class="text-muted small mb-4">Resetting password for: <strong id="resetPasswordUserName">—</strong></p>
        
        <div class="row g-4">
            <div class="col-md-12">
                <label class="form-label required fw-medium text-dark">New Password</label>
                <input class="form-control" type="password" name="password" required>
            </div>
            <div class="col-md-12">
                <label class="form-label required fw-medium text-dark">Confirm Password</label>
                <input class="form-control" type="password" name="password_confirmation" required>
            </div>
        </div>
    </x-erp.side-panel>

    {{-- Assign Role Modal --}}
    <x-erp.side-panel
        id="assignRoleOffcanvas"
        formId="assignRoleForm"
        title="Assign Role"
        action=""
        method="POST"
        width="400px"
        saveButtonText="Assign Role"
    >
        <input type="hidden" name="_method" value="PUT">
        <p class="text-muted small mb-4">Assigning role to: <strong id="assignRoleUserName">—</strong></p>
        
        <div class="row g-4">
            <div class="col-md-12">
                <label class="form-label required fw-medium text-dark">Role</label>
                <select class="form-select" name="role" required id="assignRoleSelect">
                    <option value="">Select Role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const userOffcanvas = new bootstrap.Offcanvas(document.getElementById('userOffcanvas'));
            const resetPasswordOffcanvas = new bootstrap.Offcanvas(document.getElementById('resetPasswordOffcanvas'));
            const assignRoleOffcanvas = new bootstrap.Offcanvas(document.getElementById('assignRoleOffcanvas'));
            const form = $('#userForm');
            const resetPasswordForm = $('#resetPasswordForm');
            const assignRoleForm = $('#assignRoleForm');

            const table = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('admin.users.data') }}',
                    data: function (d) {
                        d.role = $('#filterRole').val();
                        d.status = $('#filterStatus').val();
                        d.school_id = $('#filterSchool').val();
                        d.created_from = $('#filterCreatedFrom').val();
                        d.created_to = $('#filterCreatedTo').val();
                    }
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email'},
                    {data: 'phone', name: 'phone'},
                    {data: 'role', name: 'role', orderable: false, searchable: false},
                    {data: 'school', name: 'school', orderable: false, searchable: false},
                    {data: 'is_active', name: 'is_active',},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ],
                order: [[0, 'desc']],
            });

            // Filters
            $('#applyFilters').on('click', () => table.ajax.reload(null, false));
            $('#clearFilters').on('click', () => {
                $('#filterRole, #filterStatus, #filterSchool').val('');
                $('#filterCreatedFrom, #filterCreatedTo').val('');
                table.ajax.reload(null, false);
            });

            // Create User
            $('#createUser').on('click', () => {
                form[0].reset();
                $('#userMethod').val('POST');
                form.attr('action', '{{ route('admin.users.store') }}');
                $('#userOffcanvasTitle').text('Add User');
                $('#passwordLabel, #passwordConfirmLabel').addClass('required').siblings('input').prop('required', true);
                $('#userPassword, #userPasswordConfirmation').closest('.col-md-4').show();
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
                $('#avatarPreview').hide();
                form.data('is-dirty', false);
                userOffcanvas.show();
            });

            // Edit User
            $('#usersTable').on('click', '.edit-user', function () {
                $.get($(this).data('url'), (response) => {
                    form[0].reset();
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    form.attr('action', $(this).data('update-url'));
                    $('#userMethod').val('PUT');
                    $('#userOffcanvasTitle').text('Edit User');
                    // Hide password fields on edit
                    $('#passwordLabel, #passwordConfirmLabel').removeClass('required');
                    $('#userPassword, #userPasswordConfirmation').removeAttr('required').val('').closest('.col-md-4').hide();

                    const data = response.data;
                    form.find('[name="name"]').val(data.name);
                    form.find('[name="email"]').val(data.email);
                    form.find('[name="phone"]').val(data.phone);
                    form.find('[name="role"]').val(data.role);
                    form.find('[name="school_id"]').val(data.school_id);
                    form.find('[name="status"]').val(data.status);

                    if (data.avatar_url) {
                        $('#avatarPreview img').attr('src', data.avatar_url);
                        $('#avatarPreview').show();
                    } else {
                        $('#avatarPreview').hide();
                    }

                    form.find('select').trigger('change.select2');
                    form.data('is-dirty', false);
                    userOffcanvas.show();
                });
            });

            // Delete User
            $('#usersTable').on('click', '.delete-user', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false),
                });
            });

            // Toggle Status
            $('#usersTable').on('click', '.toggle-status-btn', function () {
                const url = $(this).data('url');
                $.ajax({
                    url: url,
                    type: 'PUT',
                    data: {_token: '{{ csrf_token() }}'},
                    success(response) {
                        App.toast('success', response.message);
                        table.ajax.reload(null, false);
                    },
                    error(xhr) {
                        App.toast('error', xhr.responseJSON?.message || 'Action failed.');
                    }
                });
            });

            // Reset Password
            $('#usersTable').on('click', '.reset-password-btn', function () {
                resetPasswordForm[0].reset();
                resetPasswordForm.attr('action', $(this).data('url'));
                resetPasswordForm.find('.is-invalid').removeClass('is-invalid');
                resetPasswordForm.find('.invalid-feedback.dynamic').remove();
                $('#resetPasswordUserName').text($(this).data('name'));
                resetPasswordForm.data('is-dirty', false);
                resetPasswordOffcanvas.show();
            });

            // Assign Role
            $('#usersTable').on('click', '.assign-role-btn', function () {
                assignRoleForm[0].reset();
                assignRoleForm.attr('action', $(this).data('url'));
                assignRoleForm.find('.is-invalid').removeClass('is-invalid');
                assignRoleForm.find('.invalid-feedback.dynamic').remove();
                $('#assignRoleUserName').text($(this).data('name'));
                $('#assignRoleSelect').val($(this).data('current-role'));
                assignRoleForm.find('select').trigger('change.select2');
                assignRoleForm.data('is-dirty', false);
                assignRoleOffcanvas.show();
            });

            // Avatar preview
            $('#userAvatar').on('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        $('#avatarPreview img').attr('src', e.target.result);
                        $('#avatarPreview').show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#avatarPreview').hide();
                }
            });

            // Form success handlers
            form.on('erp:success', () => {
                userOffcanvas.hide();
                table.ajax.reload(null, false);
            });

            resetPasswordForm.on('erp:success', () => {
                resetPasswordOffcanvas.hide();
                table.ajax.reload(null, false);
            });

            assignRoleForm.on('erp:success', () => {
                assignRoleOffcanvas.hide();
                table.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush