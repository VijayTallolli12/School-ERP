@extends('layouts.admin')

@section('title', 'Teacher Attendance')
@section('page-title', 'Teacher Attendance')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.teachers.index') }}">Teachers</a></li>
    <li class="breadcrumb-item active">Attendance</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0"><i class="ti ti-user-check text-primary me-2"></i>Teacher Attendance</h3>
            @can('attendance.create')
                <button class="btn btn-primary btn-sm ms-auto" id="createAttendance">Mark Attendance</button>
            @endcan
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered w-100" id="attendanceTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Teacher</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Marked By</th>
                    <th width="120">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <x-erp.side-panel 
        id="attendanceOffcanvas" 
        formId="attendanceForm" 
        title="Mark Attendance"
        action="{{ route('admin.teachers.attendance.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Save Attendance"
    >
        <input type="hidden" name="_method" value="POST" id="attendanceMethod">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Attendance Details</h6>
        
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
                <label class="form-label required fw-medium text-dark">Attendance Date</label>
                <input class="form-control" type="date" name="attendance_date" required>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Status</label>
                <select class="form-select" name="status" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
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
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('attendanceOffcanvas'));
            const form = $('#attendanceForm');
            const table = $('#attendanceTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.teachers.attendance.data') }}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'teacher_name', name: 'teacher_name'},
                    {data: 'attendance_date', name: 'attendance_date'},
                    {data: 'status', name: 'status', orderable: false, searchable: false},
                    {data: 'marked_by', name: 'marked_by'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ],
            });

            $('#createAttendance').on('click', () => {
                form[0].reset();
                $('#attendanceMethod').val('POST');
                form.attr('action', '{{ route('admin.teachers.attendance.store') }}');
                $('#attendanceOffcanvasTitle').text('Mark Attendance');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
                form.data('is-dirty', false);
                offcanvas.show();
            });

            $('#attendanceTable').on('click', '.edit-attendance', function () {
                $.get($(this).data('url'), (response) => {
                    form[0].reset();
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    form.attr('action', $(this).data('update-url'));
                    $('#attendanceMethod').val('PUT');
                    $('#attendanceOffcanvasTitle').text('Edit Attendance');

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

            $('#attendanceTable').on('click', '.delete-attendance', function () {
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
