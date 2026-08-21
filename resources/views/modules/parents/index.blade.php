@extends('layouts.admin')

@section('title', 'Parents')
@section('page-title', 'Parent Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Parents</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title fw-semibold mb-0"><i class="ti ti-users-group text-primary me-2"></i>Parents</h3>
            @can('parents.create')
                <button class="btn btn-primary btn-sm ms-auto" id="createParent">Add Parent</button>
            @endcan
        </div>

        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">All</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" id="filterSearch" placeholder="Search by name or email">
                </div>
            </div>

            <table class="table table-striped table-bordered w-100" id="parentsTable">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th width="120">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <x-erp.side-panel 
        id="parentOffcanvas" 
        formId="parentForm" 
        title="Add Parent"
        subtitle="Create and manage parent information"
        action="{{ route('admin.parents.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Save Parent"
        :hasTabs="true"
    >
        <x-slot name="tabs">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-medium px-4" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">Basic Info</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-medium px-4" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button" role="tab">Students</button>
                </li>
            </ul>
        </x-slot>

        <input type="hidden" name="_method" value="POST" id="parentMethod">
        <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Parent Information</h6>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label required fw-medium text-dark">First Name</label>
                        <input class="form-control" name="first_name" required maxlength="255">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required fw-medium text-dark">Last Name</label>
                        <input class="form-control" name="last_name" required maxlength="255">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required fw-medium text-dark">Email</label>
                        <input class="form-control" type="email" name="email" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-dark">Phone</label>
                        <input class="form-control" name="phone" maxlength="20">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-dark">Occupation</label>
                        <input class="form-control" name="occupation" maxlength="255">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required fw-medium text-dark">Status</label>
                        <select class="form-select" name="status" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected($status === 'active')>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium text-dark">Address</label>
                        <textarea class="form-control" name="address" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="students" role="tabpanel">
                <div id="studentSelections">
                    <p class="text-muted">Select students to associate with this parent.</p>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-3" id="addStudent">Add Student</button>
            </div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const filterStatus = $('#filterStatus');
            const filterSearch = $('#filterSearch');
            const parentOffcanvasElement = document.getElementById('parentOffcanvas');
            const parentOffcanvas = new bootstrap.Offcanvas(parentOffcanvasElement);
            const parentForm = $('#parentForm');
            let studentCounter = 0;

            const parentsTable = $('#parentsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('admin.parents.data') }}',
                    data: function (d) {
                        d.status = filterStatus.val();
                        d.search = filterSearch.val();
                    },
                },
                columns: [
                    {data: 'full_name', name: 'first_name'},
                    {data: 'email', name: 'email'},
                    {data: 'phone', name: 'phone'},
                    {data: 'students_count', name: 'students_count', orderable: false, searchable: false},
                    {data: 'status_label', name: 'status', orderable: false, searchable: false},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ],
            });

            [filterStatus, filterSearch].forEach((control) => {
                control.on('change input', () => parentsTable.ajax.reload());
            });

            $('#createParent').on('click', () => {
                parentForm[0].reset();
                $('#parentMethod').val('POST');
                parentForm.attr('action', '{{ route('admin.parents.store') }}');
                $('#parentOffcanvasTitle').text('Add Parent');
                parentForm.find('.is-invalid').removeClass('is-invalid');
                parentForm.find('.invalid-feedback.dynamic').remove();
                $('#studentSelections').html('<p class="text-muted">Select students to associate with this parent.</p>');
                studentCounter = 0;

                parentOffcanvas.show();
            });

            $('#addStudent').on('click', () => {
                studentCounter++;
                const studentHtml = `
                    <div class="student-entry mb-3 p-3 border rounded bg-body" data-id="${studentCounter}">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Student</label>
                                <select class="form-select student-select" name="student_ids[]" required>
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->admission_no }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Relationship</label>
                                <select class="form-select" name="relationships[]">
                                    <option value="father">Father</option>
                                    <option value="mother">Mother</option>
                                    <option value="guardian">Guardian</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <button type="button" class="btn btn-soft-danger remove-student p-0 d-flex align-items-center justify-content-center" title="Remove Student" style="width: 43px; height: 43px;">
                                    <i class="ti ti-trash fs-5"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                $('#studentSelections').append(studentHtml);
            });

            $(document).on('click', '.remove-student', function() {
                $(this).closest('.student-entry').remove();
            });

            $('#parentsTable').on('click', '.edit-parent', function () {
                $.get($(this).data('url'), (response) => {
                    parentForm[0].reset();
                    parentForm.find('.is-invalid').removeClass('is-invalid');
                    parentForm.find('.invalid-feedback.dynamic').remove();
                    parentForm.attr('action', $(this).data('update-url'));
                    $('#parentMethod').val('PUT');
                    $('#parentOffcanvasTitle').text('Edit Parent');

                    Object.entries(response.data).forEach(([key, value]) => {
                        if (key === 'students') {
                            $('#studentSelections').html('');
                            studentCounter = 0;

                            // Existing linked students — rendered as read-only rows
                            value.forEach((student) => {
                                studentCounter++;
                                const studentHtml = `
                                    <div class="student-entry mb-3 p-3 border rounded bg-body" data-id="${studentCounter}">
                                        <input type="hidden" name="student_ids[]" value="${student.id}">
                                        <input type="hidden" name="relationships[]" value="${student.relationship || 'guardian'}">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-md-5">
                                                <label class="form-label">Student</label>
                                                <div class="form-control-plaintext fw-semibold py-2">${student.name} (${student.admission_no || ''})</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Relationship</label>
                                                <div class="form-control-plaintext py-2 text-capitalize">${student.relationship || 'Guardian'}</div>
                                            </div>
                                            <div class="col-auto">
                                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                                <button type="button" class="btn btn-soft-danger remove-student p-0 d-flex align-items-center justify-content-center" title="Remove Student" style="width: 43px; height: 43px;">
                                                    <i class="ti ti-trash fs-5"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                $('#studentSelections').append(studentHtml);
                            });
                        } else {
                            const input = parentForm.find(`[name="${key}"]`);
                            if (input.length) {
                                input.val(value);
                            }
                        }
                    });

                    parentOffcanvas.show();
                });
            });

            $('#parentsTable').on('click', '.delete-parent', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => parentsTable.ajax.reload(null, false),
                });
            });

            parentForm.on('erp:success', () => {
                parentOffcanvas.hide();
                parentsTable.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush