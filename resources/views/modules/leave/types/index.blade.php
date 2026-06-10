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
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#leaveTypeModal" id="createLeaveType">
                                <i class="ti ti-plus me-1"></i> Add Leave Type
                            </button>
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
    <div class="modal fade" id="leaveTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="leaveTypeForm" method="POST" action="{{ route('admin.leave-types.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="leaveTypeMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="leaveTypeModalTitle">Add Leave Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label required">Name</label>
                            <input class="form-control" name="name" required maxlength="150" placeholder="e.g. Sick Leave, Casual Leave">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" maxlength="1000"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="leaveTypeActive" checked>
                                <label class="form-check-label" for="leaveTypeActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-device-floppy me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = new bootstrap.Modal('#leaveTypeModal');
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
                $('#leaveTypeModalTitle').text('Add Leave Type');
                $('#leaveTypeActive').prop('checked', true);
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
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
                    $('#leaveTypeModalTitle').text('Edit Leave Type');

                    form.find('[name="name"]').val(data.name);
                    form.find('[name="description"]').val(data.description);
                    $('#leaveTypeActive').prop('checked', data.is_active);

                    modal.show();
                });
            });

            $('#leaveTypesTable').on('click', '.delete-leave-type', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false),
                });
            });

            form.on('erp:success', () => {
                modal.hide();
                table.ajax.reload(null, false);
            });
        });
    </script>
@endpush
