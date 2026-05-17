@extends('layouts.admin')

@section('title', 'Teacher Attendance Report')
@section('page-title', 'Teacher Attendance Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.teachers.index') }}">Teachers</a></li>
    <li class="breadcrumb-item active">Attendance Report</li>
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.teachers.attendance.report') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Teacher</label>
                    <select class="form-select" name="teacher_id">
                        <option value="">All</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>{{ $teacher->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input class="form-control" type="date" name="from_date" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input class="form-control" type="date" name="to_date" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2 align-self-end">
                    <button class="btn btn-primary py-2 w-100" type="submit"><i class="ti ti-filter me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="fw-semibold mb-0">Attendance Records</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Teacher</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rows as $attendance)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $attendance->attendance_date->format('d-M-Y') }}</td>
                        <td>{{ $attendance->teacher->full_name }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</td>
                        <td>{{ $attendance->remarks ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No attendance records found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
