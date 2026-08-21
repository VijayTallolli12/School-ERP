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
            <h3 class="card-title mb-0"><i class="ti ti-shield text-primary me-2"></i>Role Management</h3>
            @can('roles.create')
                <button class="btn btn-primary btn-sm ms-auto" id="createRole">Add Role</button>
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
    <x-erp.side-panel 
        id="roleOffcanvas" 
        formId="roleForm" 
        title="Add Role"
        action="{{ route('admin.roles.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Save Role"
        :hasTabs="false"
        :multipart="false"
    >
        <input type="hidden" name="_method" value="POST" id="roleMethod">
        <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="basic">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Role Details</h6>
                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label required fw-medium text-dark" for="roleName">Role name</label>
                        <input id="roleName" class="form-control" type="text" name="name" required maxlength="125">
                    </div>
                    <div class="col-md-12">
                        <h6 class="fw-bold text-uppercase text-muted mb-3 mt-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Permissions</h6>
                        <div class="row g-3">
                            @foreach ($permissions as $module => $items)
                                <div class="col-md-6">
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
                </div>
            </div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('roleOffcanvas'));
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
                $('#roleOffcanvasTitle').text('Add Role');
                $('#roleForm')offcanvas.show();
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
                    $('#roleOffcanvasTitle').text('Edit Role');
                    $('#roleForm')offcanvas.show();
                });
            });

            $('#rolesTable').on('click', '.delete-role', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false)
                });
            });

            $('#roleForm').on('erp:success', () => {
                offcanvas.hide();
                table.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush
