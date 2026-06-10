@extends('modules.reports.reports_layout')

@section('title', 'Daily Attendance Report')
@section('report_title', 'Daily Attendance Report')

@section('content')
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted">{{ \Carbon\Carbon::parse($filters['date'] ?? now())->format('d M Y') }}</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('reports.attendance.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="ti ti-back-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $filters['date'] ?? now()->toDateString() }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year_id" class="form-control">
                        <option value="">All Academic Years</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Class Section</label>
                    <select name="class_section_id" class="form-control">
                        <option value="">All Classes</option>
                        @foreach ($classSections as $section)
                            <option value="{{ $section->id }}" {{ request('class_section_id') == $section->id ? 'selected' : '' }}>
                                {{ $section->schoolClass->name }} - {{ $section->section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary py-2 flex-fill">
                            <i class="ti ti-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
            <div class="row mt-3">
                <div class="col-12">
                    <a id="exportExcel" href="{{ route('reports.attendance.daily.export.excel') }}" class="btn btn-success me-2">
                        <i class="ti ti-file-type-xls me-1"></i> Export Excel
                    </a>
                    <a id="exportPdf" href="{{ route('reports.attendance.daily.export.pdf') }}" class="btn btn-danger me-2">
                        <i class="ti ti-file-type-pdf me-1"></i> Export PDF
                    </a>
                    <a id="exportPrint" href="{{ route('reports.attendance.daily.print') }}" class="btn btn-warning" target="_blank">
                        <i class="ti ti-printer me-1"></i> Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Present</p>
                            <h4 class="fw-semibold mb-0">{{ $summary['summary']['present'] ?? 0 }}</h4>
                        </div>
                        <div class="fs-32 text-success">
                            <i class="ti ti-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Absent</p>
                            <h4 class="fw-semibold mb-0">{{ $summary['summary']['absent'] ?? 0 }}</h4>
                        </div>
                        <div class="fs-32 text-danger">
                            <i class="ti ti-close"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Late</p>
                            <h4 class="fw-semibold mb-0">{{ $summary['summary']['late'] ?? 0 }}</h4>
                        </div>
                        <div class="fs-32 text-warning">
                            <i class="ti ti-time"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Leave</p>
                            <h4 class="fw-semibold mb-0">{{ $summary['summary']['leave'] ?? 0 }}</h4>
                        </div>
                        <div class="fs-32 text-info">
                            <i class="ti ti-clipboard"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Table --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-hover" id="dailyAttendanceTable">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let table = $('#dailyAttendanceTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('reports.attendance.daily_list') }}",
                    data: function(d) {
                        d.date = $('input[name="date"]').val();
                        d.academic_year_id = $('select[name="academic_year_id"]').val();
                        d.class_section_id = $('select[name="class_section_id"]').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', searchable: false, orderable: false},
                    {data: 'student_name', name: 'student_name'},
                    {data: 'class_section_name', name: 'class_section_name'},
                    {data: 'status_badge', name: 'attendance_status', orderable: false},
                    {data: 'remarks', name: 'remarks'}
                ],
                order: [[0, 'asc']],
                columnDefs: [
                    {targets: 0, orderable: false, searchable: false}
                ],
                pageLength: 25
            });

            function updateExportLinks() {
                var params = {
                    date: $('input[name="date"]').val(),
                    academic_year_id: $('select[name="academic_year_id"]').val(),
                    class_section_id: $('select[name="class_section_id"]').val()
                };
                var queryString = $.param(params);

                var baseExcel = "{{ route('reports.attendance.daily.export.excel') }}";
                var basePdf = "{{ route('reports.attendance.daily.export.pdf') }}";
                var basePrint = "{{ route('reports.attendance.daily.print') }}";

                $('#exportExcel').attr('href', baseExcel + (queryString ? '?' + queryString : ''));
                $('#exportPdf').attr('href', basePdf + (queryString ? '?' + queryString : ''));
                $('#exportPrint').attr('href', basePrint + (queryString ? '?' + queryString : ''));
            }

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.draw();
                updateExportLinks();
            });

            $('input[name="date"]').on('change', function() {
                table.draw();
                updateExportLinks();
            });

            updateExportLinks();
        });
    </script>
@endpush
