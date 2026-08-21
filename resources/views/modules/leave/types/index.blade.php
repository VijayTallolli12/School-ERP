@extends('layouts.admin')

@section('title', 'Leave Types')
@section('page-title', 'Leave Types')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Leave Types</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h3 class="card-title fw-semibold mb-0">
                        <i class="ti ti-category text-primary me-1"></i> Leave Types
                    </h3>
                    @can('leave_management.create')
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-primary" id="createLeaveType">Add Leave Type</button>
                        </div>
                    @endcan
                </div>
                <div class="card-body">
                    <table class="table table-striped table-bordered w-100" id="leaveTypesTable">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th width="140">Actions</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <x-erp.side-panel
        id="leaveTypeOffcanvas"
        formId="leaveTypeForm"
        title="Add Leave Type"
        action="{{ route('admin.leave-types.store') }}"
        method="POST"
        width="500px"
        saveButtonText="Save"
    >
        <input type="hidden" name="_method" value="POST" id="leaveTypeMethod">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Leave Type Details</h6>
        
        <div class="row g-4">
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Name</label>
                <input class="form-control" name="name" required maxlength="150" placeholder="e.g. Sick Leave, Casual Leave">
            </div>
            <div class="col-12">
                <label class="form-label fw-medium text-dark">Description</label>
                <textarea class="form-control" name="description" rows="3" maxlength="1000"></textarea>
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="leaveTypeActive" checked>
                    <label class="form-check-label fw-medium text-dark" for="leaveTypeActive">Active</label>
                </div>
            </div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('leaveTypeOffcanvas'));
            const form = $('#leaveTypeForm');

            const table = $('#leaveTypesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.leave-types.data') }}',
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'description', name: 'description', orderable: false},
                    {data: 'status_label', name: 'is_active', orderable: false, searchable: false},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ],
                order: [[0, 'asc']],
            });

            $('#createLeaveType').on('click', () => {
                form[0].reset();
                $('#leaveTypeMethod').val('POST');
                form.attr('action', '{{ route('admin.leave-types.store') }}');
                $('#leaveTypeOffcanvasTitle').text('Add Leave Type');
                $('#leaveTypeActive').prop('checked', true);
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();

                offcanvas.show();
            });

            $('#leaveTypesTable').on('click', '.edit-leave-type', function () {
                const url = $(this).data('url');
                const updateUrl = $(this).data('update-url');

                $.get(url, (response) => {
                    const data = response.data;
                    form[0].reset();
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    form.attr('action', updateUrl);
                    $('#leaveTypeMethod').val('PUT');
                    $('#leaveTypeOffcanvasTitle').text('Edit Leave Type');

                    form.find('[name="name"]').val(data.name);
                    form.find('[name="description"]').val(data.description);
                    $('#leaveTypeActive').prop('checked', data.is_active);

                    offcanvas.show();
                });
            });

            $('#leaveTypesTable').on('click', '.delete-leave-type', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false),
                });
            });

            form.on('erp:success', () => {
                offcanvas.hide();
                table.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush
