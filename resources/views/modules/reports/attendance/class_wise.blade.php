@extends('modules.reports.reports_layout')

@section('title', 'Class-wise Attendance Report')
@section('report_title', 'Class-wise Attendance Report')

@section('content')
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted">Compare attendance across all classes</p>
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
                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary py-2 w-100">
                        <i class="ti ti-filter me-1"></i> Generate Report
                    </button>
                </div>
            </form>
            @if (!empty($report['class_summary']))
                <div class="row mt-3">
                    <div class="col-12">
                        <a href="{{ route('reports.attendance.class_wise.export.excel', request()->query()) }}" class="btn btn-success me-2">
                            <i class="ti ti-file-type-xls me-1"></i> Export Excel
                        </a>
                        <a href="{{ route('reports.attendance.class_wise.export.pdf', request()->query()) }}" class="btn btn-danger me-2">
                            <i class="ti ti-file-type-pdf me-1"></i> Export PDF
                        </a>
                        <a href="{{ route('reports.attendance.class_wise.print', request()->query()) }}" class="btn btn-warning" target="_blank">
                            <i class="ti ti-printer me-1"></i> Print
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Report Table --}}
    <div class="card">
        <div class="card-header">
            <h5 class="fw-semibold card-title mb-0">
                <i class="ti ti-table text-primary me-2"></i>Attendance Summary - {{ \Carbon\Carbon::parse($filters['date'])->format('d M Y') }}
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Class Section</th>
                            <th class="text-center">Present</th>
                            <th class="text-center">Absent</th>
                            <th class="text-center">Late</th>
                            <th class="text-center">Leave</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Attendance %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['class_summary'] ?? [] as $classData)
                            @php
                                $attendancePercentage = $classData['total'] > 0 
                                    ? round(($classData['present'] / $classData['total']) * 100, 1)
                                    : 0;
                                $badgeClass = $attendancePercentage >= 80 ? 'bg-success' : ($attendancePercentage >= 60 ? 'bg-warning' : 'bg-danger');
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $classData['class_section'] }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $classData['present'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger">{{ $classData['absent'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning">{{ $classData['late'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $classData['leave'] }}</span>
                                </td>
                                <td class="text-center">
                                    <strong>{{ $classData['total'] }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $badgeClass }}">
                                        {{ $attendancePercentage }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No attendance data found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Summary Statistics --}}
    @php
        $totalAttendance = collect($report['class_summary'] ?? []);
        $totalPresent = $totalAttendance->sum('present');
        $totalAbsent = $totalAttendance->sum('absent');
        $totalLate = $totalAttendance->sum('late');
        $totalLeave = $totalAttendance->sum('leave');
        $totalRecords = $totalAttendance->sum('total');
        $overallPercentage = $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100, 1) : 0;
    @endphp

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card bg-body">
                <div class="card-body">
                    <h5 class="fw-semibold card-title mb-3"><i class="ti ti-chart-bar text-primary me-2"></i>Overall Summary</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="fs-32 text-success me-3">
                                    <i class="ti ti-check"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-1">Total Present</p>
                                    <h5 class="mb-0">{{ $totalPresent }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="fs-32 text-danger me-3">
                                    <i class="ti ti-close"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-1">Total Absent</p>
                                    <h5 class="mb-0">{{ $totalAbsent }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="fs-32 text-warning me-3">
                                    <i class="ti ti-time"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-1">Total Late</p>
                                    <h5 class="mb-0">{{ $totalLate }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="fs-32 text-info me-3">
                                    <i class="ti ti-clipboard"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-1">Total Leave</p>
                                    <h5 class="mb-0">{{ $totalLeave }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Total Attendance Records</p>
                            <h5 class="mb-0">{{ $totalRecords }}</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="text-muted mb-1">Overall Attendance Percentage</p>
                            <h5 class="mb-0">
                                <span class="badge bg-success fs-16">{{ $overallPercentage }}%</span>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                this.submit();
            });

            // Auto-load data on date change
            $('input[name="date"]').on('change', function() {
                $('#filterForm').submit();
            });
        });
    </script>
@endpush
