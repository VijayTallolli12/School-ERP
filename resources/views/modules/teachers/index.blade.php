@extends('layouts.admin')

@section('title', 'Teachers')
@section('page-title', 'Teacher Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Teachers</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title fw-semibold mb-0"><i class="ti ti-chalkboard-teacher text-primary me-2"></i>Teachers</h3>
            @can('teachers.create')
                <button class="btn btn-primary btn-sm ms-auto" id="createTeacher">Add Teacher</button>
            @endcan
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered w-100" id="teachersTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Qualification</th>
                    <th>Subjects</th>
                    <th>Classes</th>
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
        id="teacherOffcanvas" 
        formId="teacherForm" 
        title="Add Teacher"
        action="{{ route('admin.teachers.store') }}" 
        method="POST" 
        width="1100px" 
        saveButtonText="Save Teacher"
        :hasTabs="true"
        :multipart="true"
    >
        <x-slot name="tabs">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-medium px-4" data-bs-toggle="tab" data-bs-target="#teacherBasic" type="button">Basic</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-medium px-4" data-bs-toggle="tab" data-bs-target="#teacherAssignments" type="button">Assignments</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-medium px-4" data-bs-toggle="tab" data-bs-target="#teacherDocuments" type="button">Documents</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-medium px-4" data-bs-toggle="tab" data-bs-target="#teacherAccount" type="button">Account</button>
                </li>
            </ul>
        </x-slot>

        <input type="hidden" name="_method" value="POST" id="teacherMethod">
        <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="teacherBasic">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Teacher Details</h6>
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label required fw-medium text-dark">Employee ID</label>
                        <input class="form-control" name="employee_id" required maxlength="50">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required fw-medium text-dark">First Name</label>
                        <input class="form-control" name="first_name" required maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Middle Name</label>
                        <input class="form-control" name="middle_name" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required fw-medium text-dark">Last Name</label>
                        <input class="form-control" name="last_name" required maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Gender</label>
                        <select class="form-select" name="gender">
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Date of Birth</label>
                        <input class="form-control" type="date" name="date_of_birth">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Qualification</label>
                        <input class="form-control" name="qualification" maxlength="150">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Experience (years)</label>
                        <input class="form-control" type="number" min="0" max="60" name="experience_years">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Joining Date</label>
                        <input class="form-control" type="date" name="joining_date">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Phone</label>
                        <input class="form-control" name="phone" maxlength="30">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Email</label>
                        <input class="form-control" type="email" name="email" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required fw-medium text-dark">Status</label>
                        <select class="form-select" name="status" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected($status === 'active')>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium text-dark">Address</label>
                        <textarea class="form-control" name="address" rows="3"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Photo</label>
                        <input class="form-control" type="file" name="photo" accept="image/png,image/jpeg,image/webp">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="teacherAssignments">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Assignments</h6>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-dark">Assign Subjects</label>
                        <select class="form-select" name="subject_ids[]" multiple>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-dark">Assign Classes / Sections</label>
                        <select class="form-select" name="class_section_ids[]" multiple>
                            @foreach ($classSections as $classSection)
                                <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium text-dark">Class Teacher For Sections</label>
                        <select class="form-select" name="class_teacher_section_ids[]" multiple>
                            @foreach ($classSections as $classSection)
                                <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="teacherDocuments">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Documents</h6>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-dark">Certificates</label>
                        <input class="form-control" type="file" name="certificates[]" multiple accept="image/png,image/jpeg,image/webp,application/pdf">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-dark">ID Proofs</label>
                        <input class="form-control" type="file" name="id_proofs[]" multiple accept="image/png,image/jpeg,image/webp,application/pdf">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="teacherAccount">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Account Settings</h6>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" value="1" name="create_user" id="createTeacherUserSwitch">
                    <label class="form-check-label fw-medium text-dark" for="createTeacherUserSwitch">Create login account for teacher</label>
                </div>
                <div class="row g-4 teacher-account-fields">
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Password</label>
                        <input class="form-control" type="password" name="password">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Confirm Password</label>
                        <input class="form-control" type="password" name="password_confirmation">
                    </div>
                </div>
            </div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('teacherOffcanvas'));
            const form = $('#teacherForm');
            const table = $('#teachersTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.teachers.data') }}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'employee_id', name: 'employee_id'},
                    {data: 'full_name', name: 'first_name'},
                    {data: 'qualification', name: 'qualification'},
                    {data: 'subjects', name: 'subjects', orderable: false, searchable: false},
                    {data: 'classes', name: 'classes', orderable: false, searchable: false},
                    {data: 'status', name: 'status'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ]
            });

            $('#createTeacher').on('click', () => {
                form[0].reset();
                $('#teacherMethod').val('POST');
                form.attr('action', '{{ route('admin.teachers.store') }}');
                $('#teacherOffcanvasTitle').text('Add Teacher');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
                form.data('is-dirty', false);
                offcanvas.show();
            });

            $('#teachersTable').on('click', '.edit-teacher', function () {
                $.get($(this).data('url'), (response) => {
                    form[0].reset();
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    form.attr('action', $(this).data('update-url'));
                    $('#teacherMethod').val('PUT');
                    $('#teacherOffcanvasTitle').text('Edit Teacher');

                    Object.entries(response.data).forEach(([key, value]) => {
                        const field = form.find(`[name="${key}"]`);
                        if (field.length) {
                            field.val(value);
                        }
                    });

                    form.find('select[multiple]').each(function () {
                        const name = $(this).attr('name').replace('[]', '');
                        $(this).val(response.data[name] ?? []).trigger('change');
                    });
                    
                    form.find('select').trigger('change.select2');
                    form.data('is-dirty', false);
                    offcanvas.show();
                });
            });

            $('#teachersTable').on('click', '.delete-teacher', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false),
                });
            });

            form.on('erp:success', () => {
                offcanvas.hide();
                table.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush
