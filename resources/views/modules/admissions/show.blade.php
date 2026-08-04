@extends('layouts.admin')

@section('title', $admission->full_name)
@section('page-title', 'Admission Application')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.admissions.index') }}">Admissions</a></li>
    <li class="breadcrumb-item active">{{ $admission->full_name }}</li>
@endsection

@section('content')
    @php
        $colors = [
            'enquiry' => 'secondary',
            'application' => 'info',
            'verified' => 'primary',
            'approved' => 'success',
            'rejected' => 'danger',
            'converted' => 'dark',
        ];
    @endphp

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
            <h3 class="card-title mb-0">
                <i class="ti ti-clipboard-list text-primary me-2"></i>{{ $admission->full_name }}
                <span class="badge bg-{{ $colors[$admission->status] ?? 'secondary' }} ms-2">{{ $admission->status_label }}</span>
            </h3>
            <div class="ms-auto d-flex flex-wrap gap-2">
                <a href="{{ route('admin.admissions.print', $admission) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                    <i class="ti ti-printer me-1"></i> Print
                </a>
                @if ($admission->status !== 'converted' && auth()->user()->can('admissions.update'))
                    <a href="{{ route('admin.admissions.edit', $admission) }}" class="btn btn-outline-primary btn-sm">
                        <i class="ti ti-pencil me-1"></i> Edit
                    </a>
                @endif
                @if ($admission->status !== 'converted' && $admission->status !== 'rejected' && auth()->user()->can('admissions.verify'))
                    <button type="button" class="btn btn-outline-info btn-sm workflow-action" data-url="{{ route('admin.admissions.verify', $admission) }}" data-message="Verify this application?">
                        <i class="ti ti-shield-check me-1"></i> Verify
                    </button>
                @endif
                @if (in_array($admission->status, ['verified', 'application', 'enquiry'], true) && auth()->user()->can('admissions.approve'))
                    <button type="button" class="btn btn-success btn-sm workflow-action" data-url="{{ route('admin.admissions.approve', $admission) }}" data-message="Approve this application? An admission number will be generated.">
                        <i class="ti ti-circle-check me-1"></i> Approve
                    </button>
                @endif
                @if ($admission->status !== 'converted' && $admission->status !== 'rejected' && auth()->user()->can('admissions.reject'))
                    <button type="button" class="btn btn-outline-danger btn-sm reject-admission" data-url="{{ route('admin.admissions.reject', $admission) }}">
                        <i class="ti ti-x me-1"></i> Reject
                    </button>
                @endif
                @if ($canConvert && auth()->user()->can('admissions.convert'))
                    <button type="button" class="btn btn-primary btn-sm convert-admission" data-url="{{ route('admin.admissions.convert', $admission) }}">
                        <i class="ti ti-user-check me-1"></i> Convert to Student
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if ($admission->status === 'converted' && $admission->student)
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <i class="ti ti-user-check flex-shrink-0"></i>
                    <span>This application was converted to a student record.</span>
                    <a href="{{ route('admin.students.show', $admission->student) }}" class="ms-auto fw-semibold">View Student Profile <i class="ti ti-arrow-right"></i></a>
                </div>
            @endif
            @if ($admission->status === 'rejected')
                <div class="alert alert-danger d-flex align-items-center gap-2">
                    <i class="ti ti-alert-triangle flex-shrink-0"></i>
                    <span>This application has been rejected.</span>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-lg-6">
                    <h6 class="text-uppercase text-muted mb-2"><i class="ti ti-user me-1"></i>Applicant Details</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Admission No</dt>
                        <dd class="col-sm-8">{{ $admission->admission_no ?: '— (generated on approval)' }}</dd>
                        <dt class="col-sm-4">Date of Birth</dt>
                        <dd class="col-sm-8">{{ $admission->date_of_birth?->toDateString() ?: '—' }}</dd>
                        <dt class="col-sm-4">Gender</dt>
                        <dd class="col-sm-8">{{ ucfirst($admission->gender) }}</dd>
                        <dt class="col-sm-4">Blood Group</dt>
                        <dd class="col-sm-8">{{ $admission->blood_group ?: '—' }}</dd>
                        <dt class="col-sm-4">Nationality</dt>
                        <dd class="col-sm-8">{{ $admission->nationality ?: '—' }}</dd>
                        <dt class="col-sm-4">Religion</dt>
                        <dd class="col-sm-8">{{ $admission->religion ?: '—' }}</dd>
                        <dt class="col-sm-4">Category</dt>
                        <dd class="col-sm-8">{{ $admission->category ?: '—' }}</dd>
                        <dt class="col-sm-4">Aadhar No</dt>
                        <dd class="col-sm-8">{{ $admission->aadhar_no ?: '—' }}</dd>
                        <dt class="col-sm-4">Current Address</dt>
                        <dd class="col-sm-8">{{ $admission->current_address ?: '—' }}</dd>
                        <dt class="col-sm-4">Permanent Address</dt>
                        <dd class="col-sm-8">{{ $admission->permanent_address ?: '—' }}</dd>
                    </dl>
                </div>
                <div class="col-lg-6">
                    <h6 class="text-uppercase text-muted mb-2"><i class="ti ti-school me-1"></i>Academic & Guardian</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Requested Class</dt>
                        <dd class="col-sm-8">
                            @if ($admission->classSection)
                                {{ $admission->classSection->schoolClass->name }} - {{ $admission->classSection->section->name }}
                            @else
                                —
                            @endif
                        </dd>
                        <dt class="col-sm-4">Academic Year</dt>
                        <dd class="col-sm-8">{{ $admission->academicYear?->name ?: '—' }}</dd>
                        <dt class="col-sm-4">Source</dt>
                        <dd class="col-sm-8">{{ $admission->source_label }}</dd>
                        <dt class="col-sm-4">Applied On</dt>
                        <dd class="col-sm-8">{{ $admission->applied_on?->toDateString() ?: '—' }}</dd>
                        <dt class="col-sm-4">Guardian</dt>
                        <dd class="col-sm-8">{{ $admission->guardian_name ?: '—' }}</dd>
                        <dt class="col-sm-4">Relation</dt>
                        <dd class="col-sm-8">{{ $admission->guardian_relation ?: '—' }}</dd>
                        <dt class="col-sm-4">Guardian Phone</dt>
                        <dd class="col-sm-8">{{ $admission->guardian_phone ?: '—' }}</dd>
                        <dt class="col-sm-4">Guardian Email</dt>
                        <dd class="col-sm-8">{{ $admission->guardian_email ?: '—' }}</dd>
                        <dt class="col-sm-4">Occupation</dt>
                        <dd class="col-sm-8">{{ $admission->guardian_occupation ?: '—' }}</dd>
                        <dt class="col-sm-4">Remarks</dt>
                        <dd class="col-sm-8">{{ $admission->remarks ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->can('admissions.verify') || auth()->user()->can('admissions.delete'))
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="ti ti-file-text text-primary me-2"></i>Documents</h3>
                @if ($admission->status !== 'converted' && auth()->user()->can('admissions.update'))
                    <button class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#documentModal">
                        <i class="ti ti-plus me-1"></i> Upload Document
                    </button>
                @endif
            </div>
            <div class="card-body">
                @if ($admission->documents->isEmpty())
                    <p class="text-muted mb-0">No documents uploaded yet.</p>
                @else
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                        <tr>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($admission->documents as $document)
                            <tr>
                                <td>{{ $documentTypes[$document->document_type] ?? $document->document_type }}</td>
                                <td>{{ $document->document_name }}</td>
                                <td>
                                    @if ($document->verified)
                                        <span class="badge bg-success">Verified</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ Storage::url($document->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="ti ti-eye"></i></a>
                                        @if (! $document->verified && auth()->user()->can('admissions.verify'))
                                            <button type="button" class="btn btn-sm btn-outline-success verify-document" data-url="{{ route('admin.admissions.documents.verify', [$admission, $document]) }}">
                                                <i class="ti ti-shield-check"></i>
                                            </button>
                                        @endif
                                        @if (auth()->user()->can('admissions.delete'))
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-document" data-url="{{ route('admin.admissions.documents.destroy', [$admission, $document]) }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif
@endsection

@push('modals')
    <div class="modal fade" id="documentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.admissions.documents.store', $admission) }}" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Document Type</label>
                        <select class="form-select" name="document_type" required>
                            <option value="">Select</option>
                            @foreach ($documentTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Document Name</label>
                        <input class="form-control" name="document_name" maxlength="150">
                    </div>
                    <div>
                        <label class="form-label required">File</label>
                        <input class="form-control" type="file" name="file" accept="image/png,image/jpeg,image/webp,application/pdf" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const confirmAction = (url, message, extra = {}) => {
                if (!confirm(message)) return;
                $.ajax({
                    url,
                    method: 'POST',
                    data: {...extra, _token: '{{ csrf_token() }}'},
                    success: (res) => {
                        if (res.success) {
                            alert(res.message || 'Done.');
                            window.location.reload();
                        } else {
                            alert(res.message || 'Action failed.');
                        }
                    },
                    error: (xhr) => {
                        const res = xhr.responseJSON || {};
                        alert(res.message || 'Something went wrong.');
                    }
                });
            };

            $('.workflow-action').on('click', function () {
                confirmAction($(this).data('url'), $(this).data('message') || 'Confirm action?');
            });

            $('.convert-admission').on('click', function () {
                confirmAction($(this).data('url'), 'Convert this application to a student record? This will create the student, assign fees and notify guardians.');
            });

            $('.reject-admission').on('click', function () {
                const reason = prompt('Reason for rejection:');
                confirmAction($(this).data('url'), 'Reject this application?', {reason});
            });

            $('.verify-document').on('click', function () {
                confirmAction($(this).data('url'), 'Verify this document?');
            });

            $('.delete-document').on('click', function () {
                const url = $(this).data('url');
                if (!confirm('Delete this document?')) return;
                $.ajax({
                    url,
                    method: 'DELETE',
                    data: {_token: '{{ csrf_token() }}'},
                    success: () => window.location.reload(),
                });
            });
        });
    </script>
@endpush
