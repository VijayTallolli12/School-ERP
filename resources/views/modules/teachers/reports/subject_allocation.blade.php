@extends('layouts.admin')

@section('title', 'Teacher Subject Allocation')
@section('page-title', 'Teacher Subject Allocation Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.teachers.index') }}">Teachers</a></li>
    <li class="breadcrumb-item active">Subject Allocation</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="fw-semibold mb-0"><i class="ti ti-book text-primary me-2"></i>Subject Allocation</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Teacher</th>
                    <th>Employee ID</th>
                    <th>Subjects</th>
                    <th>Assigned Classes</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($teachers as $teacher)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $teacher->full_name }}</td>
                        <td>{{ $teacher->employee_id }}</td>
                        <td>{{ $teacher->subjects->pluck('name')->join(', ') ?: '-' }}</td>
                        <td>{{ $teacher->classSections->map(fn ($section) => $section->schoolClass->name.' - '.$section->section->name)->join(', ') ?: '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
