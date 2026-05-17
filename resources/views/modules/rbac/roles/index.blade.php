@extends('layouts.admin')

@section('title', 'Roles')
@section('page-title', 'Roles & Permissions')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Roles</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0">Role Management</h3>
            @can('roles.create')
                <button class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#roleModal" id="createRole">
                    <i class="ti ti-plus me-1"></i> Add Role
                </button>
            @endcan
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered w-100" id="rolesTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Permissions</th>
                    <th>Preview</th>
                    <th width="120">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="roleForm" method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="roleMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalTitle">Add Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required" for="roleName">Role name</label>
                        <input id="roleName" class="form-control" type="text" name="name" required maxlength="125">
                    </div>
                    <div class="row g-3">
                        @foreach ($permissions as $module => $items)
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100 bg-body">
                                    <div class="fw-semibold mb-2">{{ str($module)->headline() }}</div>
                                    @foreach ($items as $permission)
                                        <div class="form-check">
                                            <input class="form-check-input permission-check" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="permission_{{ $permission->id }}">
                                            <label class="form-check-label" for="permission_{{ $permission->id }}">{{ $permission->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-device-floppy me-1"></i> Save Role</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = new bootstrap.Modal('#roleModal');
            const table = $('#rolesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.roles.data') }}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'permissions_count', name: 'permissions_count', searchable: false},
                    {data: 'permissions_preview', name: 'permissions_preview', orderable: false},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ]
            });

            $('#createRole').on('click', () => {
                $('#roleForm')[0].reset();
                $('.permission-check').prop('checked', false);
                $('#roleForm').attr('action', '{{ route('admin.roles.store') }}');
                $('#roleMethod').val('POST');
                $('#roleModalTitle').text('Add Role');
            });

            $('#rolesTable').on('click', '.edit-role', function () {
                $.get($(this).data('url'), (response) => {
                    const role = response.data;
                    $('#roleForm').attr('action', $(this).data('update-url'));
                    $('#roleMethod').val('PUT');
                    $('#roleName').val(role.name);
                    $('.permission-check').prop('checked', false);
                    role.permissions.forEach((permission) => {
                        $(`.permission-check[value="${permission}"]`).prop('checked', true);
                    });
                    $('#roleModalTitle').text('Edit Role');
                    modal.show();
                });
            });

            $('#rolesTable').on('click', '.delete-role', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false)
                });
            });

            $('#roleForm').on('erp:success', () => {
                modal.hide();
                table.ajax.reload(null, false);
            });
        });
    </script>
@endpush
