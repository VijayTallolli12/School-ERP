@extends('layouts.admin')

@section("title", "Class-wise Attendance Report")
@section("page-title", "Class-wise Attendance Report")

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
                <div class="col-md-4 mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year_id" class="form-select">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i> Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Class-wise Summary</h5>
            <div>
                <a href="#" class="btn btn-sm btn-danger export-pdf"><i class="ti ti-file-type-pdf me-1"></i> PDF</a>
                <a href="#" class="btn btn-sm btn-success export-excel"><i class="ti ti-file-spreadsheet me-1"></i> Excel</a>
                <a href="#" class="btn btn-sm btn-info export-print" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Class & Section</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                            <th>Leave</th>
                            <th>Total</th>
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
$(async function() {
    const DataTable = await window.lazyDT();
    let table = $('#report-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('reports.attendance.class_wise') }}",
            data: function (d) {
                d.date = $('input[name="date"]').val();
                d.academic_year_id = $('select[name="academic_year_id"]').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'class_section', name: 'class_section' },
            { data: 'present', name: 'present' },
            { data: 'absent', name: 'absent' },
            { data: 'late', name: 'late' },
            { data: 'leave', name: 'leave' },
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

    function updateExportLinks() {
        var params = $('#filter-form').serialize();
        $('.export-pdf').attr('href', "{{ route('reports.attendance.class_wise.export.pdf') }}?" + params);
        $('.export-excel').attr('href', "{{ route('reports.attendance.class_wise.export.excel') }}?" + params);
        $('.export-print').attr('href', "{{ route('reports.attendance.class_wise.print') }}?" + params);
    }

    $('#filter-form select, #filter-form input').on('change', function() {
        table.draw();
        updateExportLinks();
    });

    updateExportLinks();
});
</script>
@endpush
