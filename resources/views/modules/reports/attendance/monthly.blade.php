@extends('modules.reports.reports_layout')

@section('title', 'Monthly Attendance Report')
@section('report_title', 'Monthly Attendance Report')

@section('content')
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted">View attendance patterns and trends</p>
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
                <div class="col-md-2">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-control">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromFormat('m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <input type="number" name="year" class="form-control" min="2020" max="{{ now()->year + 1 }}" value="{{ $year }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Class Section</label>
                    <select name="class_section_id" class="form-control" required>
                        <option value="">-- Select Class --</option>
                        @foreach ($classSections as $section)
                            <option value="{{ $section->id }}" {{ $classSectionId == $section->id ? 'selected' : '' }}>
                                {{ $section->schoolClass->name }} - {{ $section->section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary py-2 w-100">
                        <i class="ti ti-filter me-1"></i> Generate Report
                    </button>
                </div>
            </form>
            @if (!empty($report))
                <div class="row mt-3">
                    <div class="col-12">
                        <a href="{{ route('reports.attendance.monthly.export.excel', request()->query()) }}" class="btn btn-success me-2">
                            <i class="ti ti-file-type-xls me-1"></i> Export Excel
                        </a>
                        <a href="{{ route('reports.attendance.monthly.export.pdf', request()->query()) }}" class="btn btn-danger me-2">
                            <i class="ti ti-file-type-pdf me-1"></i> Export PDF
                        </a>
                        <a href="{{ route('reports.attendance.monthly.print', request()->query()) }}" class="btn btn-warning" target="_blank">
                            <i class="ti ti-printer me-1"></i> Print
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if (!empty($report))
        {{-- Summary Card --}}
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="fw-semibold card-title mb-3">
                            {{ \Carbon\Carbon::createFromFormat('m', $month)->format('F') }} {{ $year }} Summary
                        </h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="fs-32 text-success me-3">
                                        <i class="ti ti-check"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-1">Present</p>
                                        <h5 class="mb-0">{{ $report['summary']['present'] ?? 0 }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="fs-32 text-danger me-3">
                                        <i class="ti ti-close"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-1">Absent</p>
                                        <h5 class="mb-0">{{ $report['summary']['absent'] ?? 0 }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="fs-32 text-warning me-3">
                                        <i class="ti ti-time"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-1">Late</p>
                                        <h5 class="mb-0">{{ $report['summary']['late'] ?? 0 }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="fs-32 text-info me-3">
                                        <i class="ti ti-clipboard"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-1">Leave</p>
                                        <h5 class="mb-0">{{ $report['summary']['leave'] ?? 0 }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Student Breakdown Table --}}
        <div class="card">
            <div class="card-header">
                <h5 class="fw-semibold card-title mb-0">Student-wise Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th class="text-center">Present</th>
                                <th class="text-center">Absent</th>
                                <th class="text-center">Late</th>
                                <th class="text-center">Leave</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['student_breakdown'] ?? [] as $student)
                                <tr>
                                    <td>{{ $student['student'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $student['present'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">{{ $student['absent'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning">{{ $student['late'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $student['leave'] }}</span>
                                    </td>
                                    <td class="text-center fw-bold">{{ $student['total'] }}</td>
                                    <td class="text-center">
                                        <strong>{{ $student['total'] > 0 ? round(($student['present'] / $student['total']) * 100, 1) : 0 }}%</strong>
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
    @else
        <div class="alert alert-info" role="alert">
            <i class="ti ti-info-alt me-2"></i>Please select a class section to generate the report
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                this.submit();
            });
        });
    </script>
@endpush
