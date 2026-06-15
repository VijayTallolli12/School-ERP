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
                <button class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#attendanceModal" id="createAttendance">
                    <i class="ti ti-plus me-1"></i> Mark Attendance
                </button>
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
    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="attendanceForm" method="POST" action="{{ route('admin.teachers.attendance.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="attendanceMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendanceModalTitle">Mark Attendance</h5>
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
                            <label class="form-label required">Attendance Date</label>
                            <input class="form-control" type="date" name="attendance_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Status</label>
                            <select class="form-select" name="status" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
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
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ti ti-x me-1"></i>Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const modal = new bootstrap.Modal('#attendanceModal');
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
                $('#attendanceModalTitle').text('Mark Attendance');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
            });

            $('#attendanceTable').on('click', '.edit-attendance', function () {
                $.get($(this).data('url'), (response) => {
                    form[0].reset();
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    form.attr('action', $(this).data('update-url'));
                    $('#attendanceMethod').val('PUT');
                    $('#attendanceModalTitle').text('Edit Attendance');

                    Object.entries(response.data).forEach(([key, value]) => {
                        const field = form.find(`[name="${key}"]`);
                        if (field.length) {
                            field.val(value);
                        }
                    });

                    modal.show();
                });
            });

            $('#attendanceTable').on('click', '.delete-attendance', function () {
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
