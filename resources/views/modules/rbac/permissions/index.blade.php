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
                <button class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#permissionModal" id="createPermission">
                    <i class="ti ti-plus me-1"></i> Add Permission
                </button>
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
    <div class="modal fade" id="permissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content ajax-form" id="permissionForm" method="POST" action="{{ route('admin.permissions.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="permissionMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="permissionModalTitle">Add Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label required" for="permissionName">Permission name</label>
                    <input id="permissionName" class="form-control" type="text" name="name" placeholder="module.action" required maxlength="125">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-device-floppy me-1"></i> Save Permission</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const modal = new bootstrap.Modal('#permissionModal');
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
                $('#permissionModalTitle').text('Add Permission');
            });

            $('#permissionsTable').on('click', '.edit-permission', function () {
                $.get($(this).data('url'), (response) => {
                    $('#permissionForm').attr('action', $(this).data('update-url'));
                    $('#permissionMethod').val('PUT');
                    $('#permissionName').val(response.data.name);
                    $('#permissionModalTitle').text('Edit Permission');
                    modal.show();
                });
            });

            $('#permissionsTable').on('click', '.delete-permission', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false)
                });
            });

            $('#permissionForm').on('erp:success', () => {
                modal.hide();
                table.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush
