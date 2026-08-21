@extends('layouts.admin')

@section('title', 'Transfer Certificate')
@section('page-title', 'Transfer Certificate')

@section('content')
    <style>
        @media print {
            .app-header, .app-sidebar, .app-footer, .app-content-header { display: none !important; }
            .app-main { margin: 0 !important; }
            body { background: #fff !important; }
        }
    </style>

    @php
        $session = $transfer->student->sessions->firstWhere('status', 'active') ?? $transfer->student->sessions->first();
        $guardian = $transfer->student->guardians->firstWhere('is_primary', true);
    @endphp

    <div class="card">
        <div class="card-body">
            <div class="text-center border-bottom pb-3 mb-4">
                <h3 class="mb-1">{{ setting('school_name', 'School ERP') }}</h3>
                <div class="text-muted">{{ setting('school_address', '') }}</div>
                <h5 class="mt-3 text-uppercase">Transfer Certificate</h5>
                <div>TC No: <strong>{{ $transfer->tc_no }}</strong> &nbsp;|&nbsp; Issued On: <strong>{{ $transfer->tc_issued_on?->toDateString() }}</strong></div>
            </div>

            <table class="table table-bordered table-sm">
                <tbody>
                <tr>
                    <td class="fw-semibold" width="30%">Student Name</td>
                    <td>{{ $transfer->student->full_name }}</td>
                    <td class="fw-semibold" width="20%">Admission No</td>
                    <td>{{ $transfer->student->admission_no }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Date of Birth</td>
                    <td>{{ $transfer->student->date_of_birth?->toDateString() ?: '—' }}</td>
                    <td class="fw-semibold">Gender</td>
                    <td>{{ ucfirst($transfer->student->gender) }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Class at Leaving</td>
                    <td>
                        @if ($session?->classSection)
                            {{ $session->classSection->schoolClass->name }} - {{ $session->classSection->section->name }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="fw-semibold">Academic Year</td>
                    <td>{{ $session?->academicYear?->name ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Guardian Name</td>
                    <td>{{ $guardian?->name ?: '—' }}</td>
                    <td class="fw-semibold">Guardian Phone</td>
                    <td>{{ $guardian?->phone ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Conduct</td>
                    <td>{{ $transfer->conduct ?: '—' }}</td>
                    <td class="fw-semibold">Date of Leaving</td>
                    <td>{{ $transfer->transferred_on?->toDateString() }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Destination School</td>
                    <td colspan="3">{{ $transfer->destination_school ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Reason</td>
                    <td colspan="3">{{ $transfer->reason ?: '—' }}</td>
                </tr>
                </tbody>
            </table>

            <div class="mt-5 d-flex justify-content-between">
                <div>
                    <div class="mb-5">Guardian / Parent Signature</div>
                    <div class="border-top w-50">Date</div>
                </div>
                <div>
                    <div class="mb-5">Principal / Head of School</div>
                    <div class="border-top w-50">Date</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-print-none">
        <button class="btn btn-primary" onclick="window.print()">Print</button>
        <a href="{{ route('admin.lifecycle.index') }}" class="btn btn-light">Back</a>
    </div>
@endsection
