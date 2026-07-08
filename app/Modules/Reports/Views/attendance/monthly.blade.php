@extends('layouts.admin')

@section("title", "Monthly Attendance Report")
@section("page-title", "Monthly Attendance Report")

@section("content")
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Select Parameters</h5>
            <div>
                <a href="{{ route('reports.attendance.index') }}" class="btn btn-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i> Back to Dashboard</a>
            </div>
        </div>
        <div class="card-body">
            <form id="filter-form" class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Class & Section</label>
                    <select name="class_section_id" class="form-select" required>
                        <option value="">Select Class & Section</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select" required>
                        @for($i=1; $i<=12; $i++)
                            <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>{{ date("F", mktime(0, 0, 0, $i, 10)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select" required>
                        @for($y=date("Y"); $y>=date("Y")-5; $y--)
                            <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-report me-1"></i> Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    <div id="report-results">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Monthly Summary</h5>
                <div>
                    <a href="#" class="btn btn-sm btn-danger export-pdf"><i class="ti ti-file-type-pdf me-1"></i> PDF</a>
                    <a href="#" class="btn btn-sm btn-success export-excel"><i class="ti ti-file-spreadsheet me-1"></i> Excel</a>
                    <a href="#" class="btn btn-sm btn-info export-print" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="summary-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Admission No</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Late</th>
                                <th>Leave</th>
                                <th>Total Days</th>
                                <th>Attendance %</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
<script>
$(async function() {
    const DataTable = await window.lazyDT();
    let table = null;

    function loadReport() {
        var params = {
            class_section_id: $('select[name="class_section_id"]').val(),
            month: $('select[name="month"]').val(),
            year: $('select[name="year"]').val()
        };

        if (!params.class_section_id) {
            Toastr.error('Please select a class & section');
            return;
        }

        var url = "{{ route('reports.attendance.monthly') }}?" + $.param(params);

        if (table) {
            table.ajax.url(url).load();
        } else {
            table = $('#summary-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: url,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'student_name', name: 'student_name' },
                    { data: 'admission_no', name: 'admission_no' },
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
        }

        updateExportLinks(params);
    }

    function updateExportLinks(params) {
        var qs = $.param(params);
        $('.export-pdf').attr('href', "{{ route('reports.attendance.monthly.export.pdf') }}?" + qs);
        $('.export-excel').attr('href', "{{ route('reports.attendance.monthly.export.excel') }}?" + qs);
        $('.export-print').attr('href', "{{ route('reports.attendance.monthly.print') }}?" + qs);
    }

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        loadReport();
    });

    // Auto-load on page load if class_section_id is pre-selected
    if ($('select[name="class_section_id"]').val()) {
        loadReport();
    }
});
</script>
@endpush
