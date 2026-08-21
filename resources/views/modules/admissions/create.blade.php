@extends('layouts.admin')

@section('title', 'New Admission')
@section('page-title', 'New Admission Application')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.admissions.index') }}">Admissions</a></li>
    <li class="breadcrumb-item active">New Application</li>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.admissions.store') }}" enctype="multipart/form-data" id="admissionForm">
        @csrf
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="ti ti-user-plus text-primary me-2"></i>Applicant Information</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">First Name</label>
                        <input class="form-control @error('first_name') is-invalid @enderror" name="first_name" value="{{ old('first_name') }}" required maxlength="100">
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Middle Name</label>
                        <input class="form-control" name="middle_name" value="{{ old('middle_name') }}" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Last Name</label>
                        <input class="form-control" name="last_name" value="{{ old('last_name') }}" maxlength="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date of Birth</label>
                        <input class="form-control" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Gender</label>
                        <select class="form-select" name="gender" required>
                            <option value="">Select</option>
                            <option value="male" @selected(old('gender') === 'male')>Male</option>
                            <option value="female" @selected(old('gender') === 'female')>Female</option>
                            <option value="other" @selected(old('gender') === 'other')>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Blood Group</label>
                        <input class="form-control" name="blood_group" value="{{ old('blood_group') }}" maxlength="10">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nationality</label>
                        <input class="form-control" name="nationality" value="{{ old('nationality', 'Indian') }}" maxlength="80">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Religion</label>
                        <input class="form-control" name="religion" value="{{ old('religion') }}" maxlength="80">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <input class="form-control" name="category" value="{{ old('category') }}" maxlength="80">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Caste</label>
                        <input class="form-control" name="caste" value="{{ old('caste') }}" maxlength="80">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mother Tongue</label>
                        <input class="form-control" name="mother_tongue" value="{{ old('mother_tongue') }}" maxlength="80">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Aadhar No</label>
                        <input class="form-control" name="aadhar_no" value="{{ old('aadhar_no') }}" maxlength="20">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Applicant Photo</label>
                        <input class="form-control" type="file" name="photo" accept="image/png,image/jpeg,image/webp">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Current Address</label>
                        <textarea class="form-control" name="current_address" rows="3" maxlength="2000">{{ old('current_address') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Permanent Address</label>
                        <textarea class="form-control" name="permanent_address" rows="3" maxlength="2000">{{ old('permanent_address') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="ti ti-school text-primary me-2"></i>Academic Information</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Academic Year</label>
                        <select class="form-select" name="academic_year_id">
                            <option value="">Select</option>
                            @foreach ($academicYears as $academicYear)
                                <option value="{{ $academicYear->id }}" @selected(old('academic_year_id') == $academicYear->id || $academicYear->is_active)>{{ $academicYear->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Requested Class</label>
                        <select class="form-select" name="class_section_id">
                            <option value="">Select</option>
                            @foreach ($classSections as $classSection)
                                <option value="{{ $classSection->id }}" @selected(old('class_section_id') == $classSection->id)>{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Source</label>
                        <select class="form-select" name="source">
                            @foreach ($sources as $key => $label)
                                <option value="{{ $key }}" @selected(old('source', 'walk_in') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Application Date</label>
                        <input class="form-control" type="date" name="applied_on" value="{{ old('applied_on', now()->toDateString()) }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Remarks</label>
                        <input class="form-control" name="remarks" value="{{ old('remarks') }}" maxlength="2000">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="ti ti-users text-primary me-2"></i>Guardian Information</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Guardian Name</label>
                        <input class="form-control" name="guardian_name" value="{{ old('guardian_name') }}" maxlength="150">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Relation</label>
                        <select class="form-select" name="guardian_relation">
                            <option value="">Select</option>
                            <option value="father" @selected(old('guardian_relation') === 'father')>Father</option>
                            <option value="mother" @selected(old('guardian_relation') === 'mother')>Mother</option>
                            <option value="guardian" @selected(old('guardian_relation') === 'guardian')>Guardian</option>
                            <option value="other" @selected(old('guardian_relation') === 'other')>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Guardian Phone</label>
                        <input class="form-control" name="guardian_phone" value="{{ old('guardian_phone') }}" maxlength="30">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Guardian Email</label>
                        <input class="form-control" type="email" name="guardian_email" value="{{ old('guardian_email') }}" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Guardian Occupation</label>
                        <input class="form-control" name="guardian_occupation" value="{{ old('guardian_occupation') }}" maxlength="120">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body d-flex justify-content-end gap-2">
                <a href="{{ route('admin.admissions.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Application</button>
            </div>
        </div>
    </form>
@endsection
