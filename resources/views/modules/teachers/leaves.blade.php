@extends('layouts.admin')

@section('title', 'Teacher Leaves')
@section('page-title', 'Teacher Leave Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.teachers.index') }}">Teachers</a></li>
    <li class="breadcrumb-item active">Leaves</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0">Teacher Leave Requests</h3>
            @can('teachers.create')
                <button class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#leaveModal" id="createLeave">
                    <i class="ti ti-plus me-1"></i> New Leave Request
                </button>
            @endcan
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered w-100" id="leaveTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Teacher</th>
                    <th>Leave Type</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th>Approved By</th>
                    <th width="120">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <div class="modal fade" id="leaveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="leaveForm" method="POST" action="{{ route('admin.teachers.leaves.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="leaveMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="leaveModalTitle">Create Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Teacher</label>
                            <select class="form-select" name="teacher_id" required>
                                <option value="">Select</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Leave Type</label>
                            <select class="form-select" name="leave_type" required>
                                <option value="">Select</option>
                                <option value="sick">Sick</option>
                                <option value="casual">Casual</option>
                                <option value="personal">Personal</option>
                                <option value="maternity">Maternity</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Start Date</label>
                            <input class="form-control" type="date" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">End Date</label>
                            <input class="form-control" type="date" name="end_date" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reason</label>
                            <textarea class="form-control" name="reason" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Leave Request</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = new bootstrap.Modal('#leaveModal');
            const form = $('#leaveForm');
            const table = $('#leaveTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.teachers.leaves.data') }}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'teacher_name', name: 'teacher_name'},
                    {data: 'leave_type', name: 'leave_type'},
                    {data: 'period', name: 'period', orderable: false, searchable: false},
                    {data: 'status', name: 'status', orderable: false, searchable: false},
                    {data: 'approved_by', name: 'approved_by'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ],
            });

            $('#createLeave').on('click', () => {
                form[0].reset();
                $('#leaveMethod').val('POST');
                form.attr('action', '{{ route('admin.teachers.leaves.store') }}');
                $('#leaveModalTitle').text('Create Leave Request');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
            });

            $('#leaveTable').on('click', '.edit-leave', function () {
                $.get($(this).data('url'), (response) => {
                    form[0].reset();
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    form.attr('action', $(this).data('update-url'));
                    $('#leaveMethod').val('PUT');
                    $('#leaveModalTitle').text('Edit Leave Request');

                    Object.entries(response.data).forEach(([key, value]) => {
                        const field = form.find(`[name="${key}"]`);
                        if (field.length) {
                            field.val(value);
                        }
                    });

                    modal.show();
                });
            });

            $('#leaveTable').on('click', '.delete-leave', function () {
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
