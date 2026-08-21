@extends('layouts.admin')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">My Profile</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-xl bg-primary text-white mx-auto mb-3">{{ strtoupper(substr($teacher->full_name, 0, 2)) }}</div>
                    <h4 class="mb-0">{{ $teacher->full_name }}</h4>
                    <span class="text-muted">{{ $teacher->employee_id }}</span>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Subjects</h5>
                    @forelse ($teacher->subjects as $subject)
                        <span class="badge bg-info-subtle text-info me-1">{{ $subject->name }}</span>
                    @empty
                        <span class="text-muted">No subjects assigned.</span>
                    @endforelse
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Class Sections</h5>
                    @forelse ($teacher->classSections as $classSection)
                        <span class="badge bg-success-subtle text-success me-1 mb-1">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</span>
                    @empty
                        <span class="text-muted">No class sections assigned.</span>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="ti ti-user text-primary me-2"></i>Profile Information</h3>
                </div>
                <div class="card-body">
                    <form class="ajax-form" id="myProfileForm" method="POST" action="{{ route('admin.teachers.my-profile.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input class="form-control" name="phone" value="{{ $teacher->phone }}" placeholder="Contact number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Qualification</label>
                                <input class="form-control" name="qualification" value="{{ $teacher->qualification }}" placeholder="e.g. M.Sc., B.Ed.">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="3" placeholder="Residential address">{{ $teacher->address }}</textarea>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection