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
                    <i class="ti ti-arrow-left me-1"></i> Back to Attendance Reports
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
                    <select name="academic_year_id" class="form-select">
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
                    <select name="class_section_id" class="form-select">
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
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="ti ti-filter me-1"></i> Filter
                        </button>
                    <x-erp.export-buttons 
                        excelUrl="{{ route('reports.attendance.daily.export.excel') }}"
                        pdfUrl="{{ route('reports.attendance.daily.export.pdf') }}"
                        printUrl="{{ route('reports.attendance.daily.print') }}"
                        excelId="exportExcel"
                        pdfId="exportPdf"
                        printId="exportPrint"
                    />
                    </div>
                </div>
            </form>
            <div class="row mt-3">
                </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ $summary['summary']['present'] ?? 0 }}</div>
                    <div class="hero-label">Present</div>
                </div>
                <div class="hero-icon success"><i class="ti ti-check"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ $summary['summary']['absent'] ?? 0 }}</div>
                    <div class="hero-label">Absent</div>
                </div>
                <div class="hero-icon danger"><i class="ti ti-close"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ $summary['summary']['late'] ?? 0 }}</div>
                    <div class="hero-label">Late</div>
                </div>
                <div class="hero-icon warning"><i class="ti ti-time"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ $summary['summary']['leave'] ?? 0 }}</div>
                    <div class="hero-label">Leave</div>
                </div>
                <div class="hero-icon info"><i class="ti ti-clipboard"></i></div>
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
        document.addEventListener('DOMContentLoaded', async function() {
            const DataTable = await window.lazyDT();
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
