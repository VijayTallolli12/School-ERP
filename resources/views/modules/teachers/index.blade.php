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
            <h3 class="card-title fw-semibold mb-0">Teachers</h3>
            @can('teachers.create')
                <button class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#teacherModal" id="createTeacher">
                    <i class="ti ti-plus me-1"></i> Add Teacher
                </button>
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
    <div class="modal fade" id="teacherModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="teacherForm" method="POST" action="{{ route('admin.teachers.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="POST" id="teacherMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="teacherModalTitle">Add Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#teacherBasic" type="button">Basic</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#teacherAssignments" type="button">Assignments</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#teacherDocuments" type="button">Documents</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#teacherAccount" type="button">Account</button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="teacherBasic">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required">Employee ID</label>
                                    <input class="form-control" name="employee_id" required maxlength="50">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">First Name</label>
                                    <input class="form-control" name="first_name" required maxlength="100">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Middle Name</label>
                                    <input class="form-control" name="middle_name" maxlength="100">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">Last Name</label>
                                    <input class="form-control" name="last_name" required maxlength="100">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender">
                                        <option value="">Select</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Birth</label>
                                    <input class="form-control" type="date" name="date_of_birth">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Qualification</label>
                                    <input class="form-control" name="qualification" maxlength="150">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Experience (years)</label>
                                    <input class="form-control" type="number" min="0" max="60" name="experience_years">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Joining Date</label>
                                    <input class="form-control" type="date" name="joining_date">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Phone</label>
                                    <input class="form-control" name="phone" maxlength="30">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email</label>
                                    <input class="form-control" type="email" name="email" maxlength="255">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">Status</label>
                                    <select class="form-select" name="status" required>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}" @selected($status === 'active')>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="3"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Photo</label>
                                    <input class="form-control" type="file" name="photo" accept="image/png,image/jpeg,image/webp">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="teacherAssignments">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Assign Subjects</label>
                                    <select class="form-select" name="subject_ids[]" multiple>
                                        @foreach ($subjects as $subject)
                                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Assign Classes / Sections</label>
                                    <select class="form-select" name="class_section_ids[]" multiple>
                                        @foreach ($classSections as $classSection)
                                            <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Class Teacher For Sections</label>
                                    <select class="form-select" name="class_teacher_section_ids[]" multiple>
                                        @foreach ($classSections as $classSection)
                                            <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="teacherDocuments">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Certificates</label>
                                    <input class="form-control" type="file" name="certificates[]" multiple accept="image/png,image/jpeg,image/webp,application/pdf">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ID Proofs</label>
                                    <input class="form-control" type="file" name="id_proofs[]" multiple accept="image/png,image/jpeg,image/webp,application/pdf">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="teacherAccount">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" value="1" name="create_user" id="createTeacherUserSwitch">
                                <label class="form-check-label" for="createTeacherUserSwitch">Create login account for teacher</label>
                            </div>
                            <div class="row g-3 teacher-account-fields">
                                <div class="col-md-3">
                                    <label class="form-label">Password</label>
                                    <input class="form-control" type="password" name="password">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Confirm Password</label>
                                    <input class="form-control" type="password" name="password_confirmation">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-device-floppy me-1"></i> Save Teacher</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = new bootstrap.Modal('#teacherModal');
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
                $('#teacherModalTitle').text('Add Teacher');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
            });

            $('#teachersTable').on('click', '.edit-teacher', function () {
                $.get($(this).data('url'), (response) => {
                    form[0].reset();
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    form.attr('action', $(this).data('update-url'));
                    $('#teacherMethod').val('PUT');
                    $('#teacherModalTitle').text('Edit Teacher');

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

                    modal.show();
                });
            });

            $('#teachersTable').on('click', '.delete-teacher', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false),
                });
            });

            form.on('erp:success', () => {
                modal.hide();
                table.ajax.reload(null, false);
            });
        });
    </script>
@endpush
