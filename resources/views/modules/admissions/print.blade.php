@extends('layouts.admin')

@section('title', 'Admission Form')
@section('page-title', 'Admission Application Form')

@section('content')
    <style>
        @media print {
            .app-header, .app-sidebar, .app-footer, .app-content-header { display: none !important; }
            .app-main { margin: 0 !important; }
            body { background: #fff !important; }
        }
    </style>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ setting('school_name', 'School ERP') }}</h4>
                    <div class="text-muted">{{ setting('school_address', '') }}</div>
                </div>
                <div class="text-end">
                    <h5 class="mb-1">Admission Application</h5>
                    <div>Application No: <strong>{{ $admission->admission_no ?: '—' }}</strong></div>
                    <div>Status: <strong>{{ $admission->status_label }}</strong></div>
                </div>
            </div>

            <h6 class="text-uppercase text-muted mb-3">Applicant Details</h6>
            <table class="table table-bordered table-sm">
                <tbody>
                <tr>
                    <td class="fw-semibold" width="25%">Full Name</td>
                    <td>{{ $admission->full_name }}</td>
                    <td class="fw-semibold" width="25%">Date of Birth</td>
                    <td>{{ $admission->date_of_birth?->toDateString() ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Gender</td>
                    <td>{{ ucfirst($admission->gender) }}</td>
                    <td class="fw-semibold">Blood Group</td>
                    <td>{{ $admission->blood_group ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Nationality</td>
                    <td>{{ $admission->nationality ?: '—' }}</td>
                    <td class="fw-semibold">Category</td>
                    <td>{{ $admission->category ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Aadhar No</td>
                    <td>{{ $admission->aadhar_no ?: '—' }}</td>
                    <td class="fw-semibold">Mother Tongue</td>
                    <td>{{ $admission->mother_tongue ?: '—' }}</td>
                </tr>
                </tbody>
            </table>

            <h6 class="text-uppercase text-muted mb-3">Academic Information</h6>
            <table class="table table-bordered table-sm">
                <tbody>
                <tr>
                    <td class="fw-semibold" width="25%">Requested Class</td>
                    <td>
                        @if ($admission->classSection)
                            {{ $admission->classSection->schoolClass->name }} - {{ $admission->classSection->section->name }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="fw-semibold" width="25%">Academic Year</td>
                    <td>{{ $admission->academicYear?->name ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Source</td>
                    <td>{{ $admission->source_label }}</td>
                    <td class="fw-semibold">Applied On</td>
                    <td>{{ $admission->applied_on?->toDateString() ?: '—' }}</td>
                </tr>
                </tbody>
            </table>

            <h6 class="text-uppercase text-muted mb-3">Guardian Information</h6>
            <table class="table table-bordered table-sm">
                <tbody>
                <tr>
                    <td class="fw-semibold" width="25%">Guardian Name</td>
                    <td>{{ $admission->guardian_name ?: '—' }}</td>
                    <td class="fw-semibold" width="25%">Relation</td>
                    <td>{{ $admission->guardian_relation ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Phone</td>
                    <td>{{ $admission->guardian_phone ?: '—' }}</td>
                    <td class="fw-semibold">Email</td>
                    <td>{{ $admission->guardian_email ?: '—' }}</td>
                </tr>
                </tbody>
            </table>

            <div class="mt-5 d-flex justify-content-between">
                <div>
                    <div class="mb-5">Guardian / Parent Signature</div>
                    <div class="border-top w-50">Date</div>
                </div>
                <div>
                    <div class="mb-5">Admission Officer</div>
                    <div class="border-top w-50">Date</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-print-none">
        <button class="btn btn-primary" onclick="window.print()">Print</button>
        <a href="{{ route('admin.admissions.show', $admission) }}" class="btn btn-light">Back</a>
    </div>
@endsection
