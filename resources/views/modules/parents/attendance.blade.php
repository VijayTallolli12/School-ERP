@extends('layouts.parent')

@section('title', 'Attendance')
@section('page-title', 'Student Attendance')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('parent-portal.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Attendance</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Student Attendance</h5>
        </div>
        <div class="card-body">
            @foreach($students as $student)
                <div class="mb-4">
                    <h6>{{ $student->full_name }} ({{ $student->admission_no }})</h6>
                    
                    @php
                        $attendanceRecords = \App\Modules\Attendance\Models\Attendance::where('student_id', $student->id)
                            ->where('academic_year_id', app(\App\Core\Tenant\SchoolContext::class)->getAcademicYearId())
                            ->orderBy('attendance_date', 'desc')
                            ->get();
                            
                        $total = $attendanceRecords->count();
                        $present = $attendanceRecords->whereIn('status', ['present', 'late', 'half_day'])->count();
                        $absent = $attendanceRecords->where('status', 'absent')->count();
                        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
                    @endphp

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="border p-2 rounded text-center">
                                <div class="text-muted small">Total Days</div>
                                <strong>{{ $total }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border p-2 rounded text-center">
                                <div class="text-muted small">Present</div>
                                <strong class="text-success">{{ $present }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border p-2 rounded text-center">
                                <div class="text-muted small">Absent</div>
                                <strong class="text-danger">{{ $absent }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border p-2 rounded text-center">
                                <div class="text-muted small">Percentage</div>
                                <strong class="text-primary">{{ $percentage }}%</strong>
                            </div>
                        </div>
                    </div>

                    @if($attendanceRecords->isEmpty())
                        <p class="text-muted">No attendance records found for this academic year.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendanceRecords->take(10) as $record)
                                        <tr>
                                            <td>{{ $record->attendance_date->format('M d, Y') }}</td>
                                            <td>
                                                @if($record->status === 'present')
                                                    <span class="badge bg-success">Present</span>
                                                @elseif($record->status === 'absent')
                                                    <span class="badge bg-danger">Absent</span>
                                                @elseif($record->status === 'late')
                                                    <span class="badge bg-warning">Late</span>
                                                @elseif($record->status === 'half_day')
                                                    <span class="badge bg-info">Half Day</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($record->status) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $record->remarks ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if($attendanceRecords->count() > 10)
                                <p class="text-muted small mt-2">Showing last 10 records.</p>
                            @endif
                        </div>
                    @endif
                </div>
                @if(!$loop->last)
                    <hr>
                @endif
            @endforeach
        </div>
    </div>
@endsection
