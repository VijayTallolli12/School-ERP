@extends('layouts.parent')

@section('title', 'Dashboard')
@section('page-title', 'Parent Dashboard')

@section('content')
    <div class="row g-4">
        <!-- Students Overview -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">My Children</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($students as $student)
                            <div class="col-md-6 col-lg-4">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $student->full_name }}</h6>
                                        <p class="card-text text-muted mb-2">
                                            Admission No: {{ $student->admission_no }}
                                        </p>
                                        <p class="card-text mb-0">
                                            Class: {{ $student->sessions->first()?->classSection?->schoolClass->name ?? 'N/A' }} -
                                            {{ $student->sessions->first()?->classSection?->section->name ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-md-6 col-lg-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-4 text-success">{{ $attendance_summary['percentage'] }}%</div>
                    <p class="card-text">Attendance</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-4 text-warning">${{ number_format($fees_summary['pending'], 2) }}</div>
                    <p class="card-text">Pending Fees</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-4 text-info">{{ $exam_results_summary['average'] }}%</div>
                    <p class="card-text">Average Score</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-4 text-primary">{{ $notifications->count() }}</div>
                    <p class="card-text">Notifications</p>
                </div>
            </div>
        </div>

        <!-- Recent Notifications -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="mb-0">Recent Notifications</h5>
                    <a href="{{ route('parent-portal.notifications') }}" class="btn btn-sm btn-outline-primary ms-auto">View All</a>
                </div>
                <div class="card-body">
                    @if($notifications->count() > 0)
                        @foreach($notifications as $notification)
                            <div class="mb-3 pb-3 border-bottom">
                                <h6 class="mb-1">{{ $notification->title }}</h6>
                                <p class="text-muted small mb-0">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No recent notifications.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="{{ route('parent-portal.attendance') }}" class="btn btn-outline-primary w-100">
                                <i class="ti ti-calendar-check fs-2 d-block mb-2"></i>
                                View Attendance
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('parent-portal.fees') }}" class="btn btn-outline-success w-100">
                                <i class="ti ti-cash fs-2 d-block mb-2"></i>
                                View Fees
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('parent-portal.exam-results') }}" class="btn btn-outline-info w-100">
                                <i class="ti ti-school fs-2 d-block mb-2"></i>
                                Exam Results
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('parent-portal.timetable') }}" class="btn btn-outline-warning w-100">
                                <i class="ti ti-clock fs-2 d-block mb-2"></i>
                                Timetable
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection