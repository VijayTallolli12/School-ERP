@extends('layouts.admin')

@section("title", "Daily Attendance Report")
@section("page-title", "Daily Attendance Report")

@section("content")
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filter Report</h5>
            <div>
                <a href="{{ route('reports.attendance.index') }}" class="btn btn-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i> Back to Dashboard</a>
            </div>
        </div>
        <div class="card-body">
            <form id="filter-form" class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year_id" class="form-select">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Class & Section</label>
                    <select name="class_section_id" class="form-select">
                        <option value="">All</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i> Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Report Results</h5>
            <div>
                <a href="#" class="btn btn-sm btn-danger export-btn" data-type="pdf"><i class="ti ti-file-type-pdf me-1"></i> PDF</a>
                <a href="#" class="btn btn-sm btn-success export-btn" data-type="excel"><i class="ti ti-file-spreadsheet me-1"></i> Excel</a>
                <a href="#" class="btn btn-sm btn-info export-btn" data-type="print" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Class & Section</th>
                            <th>Status</th>
                            <th>Date</th>
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
$(async function() {
    const DataTable = await window.lazyDT();
    let table = $('#report-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('reports.attendance.daily_list') }}",
            data: function (d) {
                d.date = $('input[name="date"]').val();
                d.academic_year_id = $('select[name="academic_year_id"]').val();
                d.class_section_id = $('select[name="class_section_id"]').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'student_name', name: 'student_name' },
            { data: 'admission_no', name: 'admission_no' },
            { data: 'class_section', name: 'class_section' },
            { 
                data: 'status', 
                name: 'status',
                render: function(data) {
                    var badges = {
                        'present': '<span class="badge bg-success">Present</span>',
                        'absent': '<span class="badge bg-danger">Absent</span>',
                        'late': '<span class="badge bg-warning text-dark">Late</span>',
                        'leave': '<span class="badge bg-info">Leave</span>',
                    };
                    return badges[data] || data;
                }
            },
            { data: 'attendance_date', name: 'attendance_date' },
        ]
    });

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        table.draw();
    });

    $('.export-btn').on('click', function(e) {
        e.preventDefault();
        let type = $(this).data('type');
        let params = $('#filter-form').serialize();
        let url = "";
        if (type === 'pdf') {
            url = "{{ route('reports.attendance.daily.export.pdf') }}";
        } else if (type === 'excel') {
            url = "{{ route('reports.attendance.daily.export.excel') }}";
        } else if (type === 'print') {
            url = "{{ route('reports.attendance.daily.print') }}";
            window.open(url + '?' + params, '_blank');
            return;
        }
        window.location.href = url + '?' + params;
    });
});
</script>
@endpush
