@extends('layouts.admin')

@section("title", "Teacher Reports Dashboard")
@section("page-title", "Teacher Reports Dashboard")

@section("content")
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Teachers</h5>
                    <p class="card-text display-4">{{ $stats['total_teachers'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Active Teachers</h5>
                    <p class="card-text display-4">{{ $stats['active_teachers'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Class Teachers Count</h5>
                    <p class="card-text display-4">{{ $stats['class_teachers'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Subject Allocations</h5>
                    <p class="card-text display-4">{{ $stats['subject_allocations'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Available Reports</h4>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('reports.teachers.list') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Teacher List Report</h5>
                            <p class="mb-1">View comprehensive list of teachers with filtering options.</p>
                        </a>
                        <a href="{{ route('reports.teachers.attendance') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Teacher Attendance Report</h5>
                            <p class="mb-1">Analyze present/absent counts and monthly attendance summary.</p>
                        </a>
                        <a href="{{ route('reports.teachers.subject_allocation') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Subject Allocation Report</h5>
                            <p class="mb-1">Analyze teacher-wise subjects and class assignments.</p>
                        </a>
                        <a href="{{ route('reports.teachers.class_teacher_mapping') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Class Teacher Mapping Report</h5>
                            <p class="mb-1">View class, section, and assigned teacher.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection