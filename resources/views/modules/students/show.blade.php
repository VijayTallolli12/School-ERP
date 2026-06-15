@extends('layouts.admin')

@section('title', 'Student Profile')
@section('page-title', 'Student Profile')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.students.index') }}">Students</a></li>
    <li class="breadcrumb-item active">{{ $student->full_name }}</li>
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row">
        {{-- Profile Header --}}
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    @if($student->photo_path)
                        <img src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->full_name }}" class="rounded-circle mb-3" width="120" height="120" style="object-fit:cover;">
                    @else
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white mb-3" style="width:120px;height:120px;font-size:36px;font-weight:600;">
                            {{ strtoupper(substr($student->first_name ?? '?', 0, 1)) }}
                        </div>
                    @endif
                    <h4 class="mb-1">{{ $student->full_name }}</h4>
                    <p class="text-muted mb-2">{{ $student->admission_no }}</p>
                    <span class="badge bg-{{ $student->status === 'active' ? 'success' : 'danger' }} mb-3">{{ ucfirst($student->status) }}</span>

                    @if($session)
                        <div class="mt-2">
                            <span class="fw-semibold">{{ $session->classSection->schoolClass->name ?? '-' }} - {{ $session->classSection->section->name ?? '-' }}</span>
                            @if($session->roll_no)
                                <span class="text-muted ms-2">Roll #{{ $session->roll_no }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Guardian Info --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-user me-2"></i>Guardian Information</h5>
                </div>
                <div class="card-body">
                    @forelse($student->guardians as $guardian)
                        <div class="d-flex align-items-start mb-3 {{ !$loop->last ? 'pb-3 border-bottom' : '' }}">
                            <div class="flex-shrink-0 me-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-info text-white" style="width:40px;height:40px;font-size:14px;font-weight:600;">
                                    {{ strtoupper(substr($guardian->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $guardian->name }}</div>
                                <div class="text-muted small">
                                    {{ $guardian->relation }}
                                    @if($guardian->is_primary) <span class="badge bg-primary ms-1">Primary</span> @endif
                                </div>
                                <div class="text-muted small mt-1">
                                    @if($guardian->phone) <i class="ti ti-phone me-1"></i> {{ $guardian->phone }} @endif
                                    @if($guardian->email) <br><i class="ti ti-mail me-1"></i> {{ $guardian->email }} @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No guardians assigned</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="col-md-8">
            {{-- Personal Info --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-id me-2"></i>Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">First Name</label>
                            <div class="fw-semibold">{{ $student->first_name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Middle Name</label>
                            <div class="fw-semibold">{{ $student->middle_name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Last Name</label>
                            <div class="fw-semibold">{{ $student->last_name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Date of Birth</label>
                            <div class="fw-semibold">{{ $student->date_of_birth?->format('d-m-Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Gender</label>
                            <div class="fw-semibold">{{ ucfirst($student->gender ?? '-') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Blood Group</label>
                            <div class="fw-semibold">{{ $student->blood_group ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Religion</label>
                            <div class="fw-semibold">{{ $student->religion ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Nationality</label>
                            <div class="fw-semibold">{{ $student->nationality ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Category</label>
                            <div class="fw-semibold">{{ $student->category ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Aadhar No</label>
                            <div class="fw-semibold">{{ $student->aadhar_no ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Academic Info --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-school me-2"></i>Academic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Admission No</label>
                            <div class="fw-semibold">{{ $student->admission_no ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Admission Date</label>
                            <div class="fw-semibold">{{ $student->admission_date?->format('d-m-Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Academic Year</label>
                            <div class="fw-semibold">{{ $session?->academicYear?->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Class & Section</label>
                            <div class="fw-semibold">{{ $session?->classSection?->schoolClass?->name ?? '-' }} - {{ $session?->classSection?->section?->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Roll Number</label>
                            <div class="fw-semibold">{{ $session?->roll_no ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Status</label>
                            <div class="fw-semibold">
                                <span class="badge bg-{{ $student->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($student->status) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            @if($student->current_address || $student->permanent_address)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ti ti-map-pin me-2"></i>Address Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if($student->current_address)
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Current Address</label>
                                    <div class="fw-semibold">{{ $student->current_address }}</div>
                                </div>
                            @endif
                            @if($student->permanent_address)
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Permanent Address</label>
                                    <div class="fw-semibold">{{ $student->permanent_address }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
