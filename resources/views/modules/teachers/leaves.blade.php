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
            <h3 class="card-title mb-0"><i class="ti ti-calendar-off text-primary me-2"></i>Teacher Leave Requests</h3>
            @can('leave_management.create')
                <button class="btn btn-primary btn-sm ms-auto" id="createLeave">New Leave Request</button>
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
    <x-erp.side-panel 
        id="leaveOffcanvas" 
        formId="leaveForm" 
        title="Create Leave Request"
        action="{{ route('admin.teachers.leaves.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Save Leave Request"
    >
        <input type="hidden" name="_method" value="POST" id="leaveMethod">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Leave Details</h6>
        
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Teacher</label>
                <select class="form-select" name="teacher_id" required>
                    <option value="">Select</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Leave Type</label>
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
                <label class="form-label required fw-medium text-dark">Start Date</label>
                <input class="form-control" type="date" name="start_date" required>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">End Date</label>
                <input class="form-control" type="date" name="end_date" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-medium text-dark">Reason</label>
                <textarea class="form-control" name="reason" rows="3"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium text-dark">Status</label>
                <select class="form-select" name="status">
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-medium text-dark">Remarks</label>
                <textarea class="form-control" name="remarks" rows="3"></textarea>
            </div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('leaveOffcanvas'));
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
                $('#leaveOffcanvasTitle').text('Create Leave Request');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
                form.data('is-dirty', false);
                offcanvas.show();
            });

            $('#leaveTable').on('click', '.edit-leave', function () {
                $.get($(this).data('url'), (response) => {
                    form[0].reset();
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    form.attr('action', $(this).data('update-url'));
                    $('#leaveMethod').val('PUT');
                    $('#leaveOffcanvasTitle').text('Edit Leave Request');

                    Object.entries(response.data).forEach(([key, value]) => {
                        const field = form.find(`[name="${key}"]`);
                        if (field.length) {
                            field.val(value);
                        }
                    });

                    form.find('select').trigger('change.select2');
                    form.data('is-dirty', false);
                    offcanvas.show();
                });
            });

            $('#leaveTable').on('click', '.delete-leave', function () {
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
