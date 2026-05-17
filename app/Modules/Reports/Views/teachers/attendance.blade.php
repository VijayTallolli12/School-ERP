@extends('layouts.admin')

@section("title", "Teacher Attendance Report")
@section("page-title", "Teacher Attendance Report")

@section("content")
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filter Report</h5>
            <div>
                <a href="{{ route('reports.teachers.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
            </div>
        </div>
        <div class="card-body">
            <form id="filter-form" class="row">
                <div class="col-md-3 mb-3">
                    <label>Teacher</label>
                    <select name="teacher_id" class="form-select">
                        <option value="">All Teachers</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->full_name }} ({{ $teacher->employee_id }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Teacher Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($teacherStatuses as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Attendance Status</label>
                    <select name="attendance_status" class="form-select">
                        <option value="">All Attendance</option>
                        @foreach($attendanceStatuses as $status)
                            <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Month</label>
                    <select name="month" class="form-select">
                        <option value="">All Months</option>
                        @for($i=1; $i<=12; $i++)
                            <option value="{{ $i }}">{{ date("F", mktime(0, 0, 0, $i, 10)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Year</label>
                    <select name="year" class="form-select">
                        <option value="">All Years</option>
                        @for($y=date("Y"); $y>=date("Y")-5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Report Results</h5>
            <div>
                <a href="#" class="btn btn-sm btn-danger export-btn" data-type="pdf"><i class="fa fa-file-pdf"></i> PDF</a>
                <a href="#" class="btn btn-sm btn-success export-btn" data-type="excel"><i class="fa fa-file-excel"></i> Excel</a>
                <a href="#" class="btn btn-sm btn-info export-btn" data-type="print" target="_blank"><i class="fa fa-print"></i> Print</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Teacher Name</th>
                            <th>Employee ID</th>
                            <th>Status</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                            <th>Half Day</th>
                            <th>Excused</th>
                            <th>Total Days</th>
                            <th>Attendance %</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
<script>
$(document).ready(function() {
    let table = $('#report-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('reports.teachers.attendance') }}",
            data: function (d) {
                d.teacher_id = $('select[name="teacher_id"]').val();
                d.status = $('select[name="status"]').val();
                d.attendance_status = $('select[name="attendance_status"]').val();
                d.month = $('select[name="month"]').val();
                d.year = $('select[name="year"]').val();
                d.from_date = $('input[name="from_date"]').val();
                d.to_date = $('input[name="to_date"]').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'teacher_name', name: 'teacher_name' },
            { data: 'employee_id', name: 'employee_id' },
            { data: 'status', name: 'status' },
            { data: 'present', name: 'present' },
            { data: 'absent', name: 'absent' },
            { data: 'late', name: 'late' },
            { data: 'half_day', name: 'half_day' },
            { data: 'excused', name: 'excused' },
            { data: 'total', name: 'total' },
            { 
                data: 'percentage', 
                name: 'percentage',
                render: function(data) {
                    return data + '%';
                }
            }
        ]
    });

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        table.draw();
    });

    $('#filter-form select, #filter-form input').on('change', function() {
        table.draw();
    });

    $('.export-btn').on('click', function(e) {
        e.preventDefault();
        let type = $(this).data('type');
        let url = "";
        if (type === 'pdf') {
            url = "{{ route('reports.teachers.export.pdf', ['type' => 'attendance']) }}";
        } else if (type === 'excel') {
            url = "{{ route('reports.teachers.export.excel', ['type' => 'attendance']) }}";
        } else if (type === 'print') {
            url = "{{ route('reports.teachers.print', ['type' => 'attendance']) }}";
            let params = $('#filter-form').serialize();
            window.open(url + '?' + params, '_blank');
            return;
        }
        let params = $('#filter-form').serialize();
        window.location.href = url + '?' + params;
    });
});
</script>
@endpush
