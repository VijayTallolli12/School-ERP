@extends('layouts.admin')

@section('title', 'Permissions')
@section('page-title', 'Permissions')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Permissions</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0"><i class="ti ti-lock text-primary me-2"></i>Permission Registry</h3>
            @can('permissions.create')
                <button class="btn btn-primary btn-sm ms-auto" id="createPermission">Add Permission</button>
            @endcan
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered w-100" id="permissionsTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Module</th>
                    <th>Name</th>
                    <th>Roles</th>
                    <th width="120">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <x-erp.side-panel 
        id="permissionOffcanvas" 
        formId="permissionForm" 
        title="Add Permission"
        action="{{ route('admin.permissions.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Save Permission"
        :hasTabs="false"
        :multipart="false"
    >
        <input type="hidden" name="_method" value="POST" id="permissionMethod">
        <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="basic">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Permission Details</h6>
                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label required fw-medium text-dark" for="permissionName">Permission name</label>
                        <input id="permissionName" class="form-control" type="text" name="name" placeholder="module.action" required maxlength="125">
                    </div>
                </div>
            </div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('permissionOffcanvas'));
            const table = $('#permissionsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.permissions.data') }}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'module', name: 'name'},
                    {data: 'name', name: 'name'},
                    {data: 'roles_count', name: 'roles_count', searchable: false},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ]
            });

            $('#createPermission').on('click', () => {
                $('#permissionForm')[0].reset();
                $('#permissionForm').attr('action', '{{ route('admin.permissions.store') }}');
                $('#permissionMethod').val('POST');
                $('#permissionOffcanvasTitle').text('Add Permission');
                $('#permissionForm')offcanvas.show();
            });

            $('#permissionsTable').on('click', '.edit-permission', function () {
                $.get($(this).data('url'), (response) => {
                    $('#permissionForm').attr('action', $(this).data('update-url'));
                    $('#permissionMethod').val('PUT');
                    $('#permissionName').val(response.data.name);
                    $('#permissionOffcanvasTitle').text('Edit Permission');
                    $('#permissionForm')offcanvas.show();
                });
            });

            $('#permissionsTable').on('click', '.delete-permission', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false)
                });
            });

            $('#permissionForm').on('erp:success', () => {
                offcanvas.hide();
                table.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush
