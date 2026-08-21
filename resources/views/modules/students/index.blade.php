@extends('layouts.admin')

@section('title', 'Students')
@section('page-title', 'Student Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Students</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0"><i class="ti ti-school text-primary me-2"></i>Students</h3>
            <div class="ms-auto d-flex gap-2">
                @can('student_documents.view')
                    <a href="{{ route('admin.documents.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ti ti-file-text me-1"></i> Documents
                    </a>
                @endcan
                @can('students.create')
                    <button class="btn btn-primary btn-sm" id="createStudent">Admit Student</button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered w-100" id="studentsTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Admission No</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Guardian</th>
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
        id="studentOffcanvas" 
        formId="studentForm" 
        title="Admit Student"
        subtitle="Manage student admission and academic details"
        action="{{ route('admin.students.store') }}" 
        method="POST" 
        width="900px" 
        saveButtonText="Save Student"
        :multipart="true"
        :hasTabs="true"
    >
        <x-slot name="tabs">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-medium px-4" data-bs-toggle="tab" data-bs-target="#studentBasic" type="button">Student</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-medium px-4" data-bs-toggle="tab" data-bs-target="#studentAcademic" type="button">Academic</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-medium px-4" data-bs-toggle="tab" data-bs-target="#studentGuardian" type="button">Guardian</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-medium px-4" data-bs-toggle="tab" data-bs-target="#studentAccount" type="button">Account</button>
                </li>
            </ul>
        </x-slot>

        <input type="hidden" name="_method" value="POST" id="studentMethod">
        <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="studentBasic">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Student Information</h6>
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label required fw-medium text-dark">Admission No</label>
                        <input class="form-control" name="admission_no" required maxlength="50" placeholder="e.g. ADM-2024-001">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Admission Date</label>
                        <input class="form-control" type="date" name="admission_date">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required fw-medium text-dark">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="alumni">Alumni</option>
                            <option value="transferred">Transferred</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required fw-medium text-dark">First Name</label>
                        <input class="form-control" name="first_name" required maxlength="100" placeholder="First name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Middle Name</label>
                        <input class="form-control" name="middle_name" maxlength="100" placeholder="Middle name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Last Name</label>
                        <input class="form-control" name="last_name" maxlength="100" placeholder="Last name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Date of Birth</label>
                        <input class="form-control" type="date" name="date_of_birth">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required fw-medium text-dark">Gender</label>
                        <select class="form-select" name="gender" required>
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Blood Group</label>
                        <input class="form-control" name="blood_group" maxlength="10" placeholder="e.g. A+">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Religion</label>
                        <input class="form-control" name="religion" maxlength="80" placeholder="Religion">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Category</label>
                        <input class="form-control" name="category" maxlength="80" placeholder="Category">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Aadhar No</label>
                        <input class="form-control" name="aadhar_no" maxlength="20" placeholder="Aadhar number">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Nationality</label>
                        <input class="form-control" name="nationality" value="Indian" maxlength="80">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Mother Tongue</label>
                        <input class="form-control" name="mother_tongue" maxlength="80" placeholder="Mother tongue">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Photo</label>
                        <input class="form-control" type="file" name="photo" accept="image/png,image/jpeg,image/webp">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-dark">Current Address</label>
                        <textarea class="form-control" name="current_address" rows="3" placeholder="Current address"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-dark">Permanent Address</label>
                        <textarea class="form-control" name="permanent_address" rows="3" placeholder="Permanent address"></textarea>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="studentAcademic">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Academic Details</h6>
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label required fw-medium text-dark">Academic Year</label>
                        <select class="form-select" name="academic_year_id" required>
                            <option value="">Select</option>
                            @foreach ($academicYears as $academicYear)
                                <option value="{{ $academicYear->id }}" @selected($academicYear->is_active)>{{ $academicYear->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required fw-medium text-dark">Class & Section</label>
                        <select class="form-select" name="class_section_id" required>
                            <option value="">Select</option>
                            @foreach ($classSections as $classSection)
                                <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Roll No</label>
                        <input class="form-control" name="roll_no" maxlength="30" placeholder="Roll number">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="studentGuardian">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Guardian Details</h6>
                <!-- Toggle: Existing Parent vs New Guardian -->
                <div class="mb-4">
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="guardian_mode" id="modeExisting" value="existing" autocomplete="off">
                        <label class="btn btn-outline-primary" for="modeExisting">
                            <i class="ti ti-user-check me-1"></i> Link Existing Parent
                        </label>
                        <input type="radio" class="btn-check" name="guardian_mode" id="modeNew" value="new" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="modeNew">
                            <i class="ti ti-user-plus me-1"></i> Create New Guardian
                        </label>
                    </div>
                </div>

                <!-- Existing Parent Selection -->
                <div id="existingParentSection" class="d-none">
                    <div class="border rounded p-4 bg-body">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label required fw-medium text-dark">Select Parent</label>
                                <select class="form-select" name="parent_id" id="parentSelect">
                                    <option value="">— Select Parent —</option>
                                    @foreach($parents as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->full_name }} ({{ $parent->email }})</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Link this student to an existing parent account. Guardian details will be taken from the parent record.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium text-dark">Relationship to Student</label>
                                <select class="form-select" name="relationship">
                                    <option value="father">Father</option>
                                    <option value="mother">Mother</option>
                                    <option value="guardian">Guardian</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Guardian Creation -->
                <div id="newGuardianSection">
                    <div class="d-flex align-items-center mb-3">
                        <h6 class="mb-0 fw-semibold">Guardians</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm ms-auto" id="addGuardian">Add Guardian</button>
                    </div>
                    <div id="guardianRows" class="vstack gap-3">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="studentAccount">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Account Settings</h6>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" value="1" name="create_user" id="createUserSwitch">
                    <label class="form-check-label fw-medium text-dark" for="createUserSwitch">Create login account for student</label>
                </div>
                <div class="row g-4 student-account-fields">
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Email</label>
                        <input class="form-control" type="email" name="email" maxlength="255" placeholder="student@school.com">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Password</label>
                        <input class="form-control" type="password" name="password" placeholder="Min. 8 characters">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Confirm Password</label>
                        <input class="form-control" type="password" name="password_confirmation" placeholder="Re-enter password">
                    </div>
                </div>
            </div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const studentOffcanvasElement = document.getElementById('studentOffcanvas');
            const offcanvas = new bootstrap.Offcanvas(studentOffcanvasElement);
            const form = $('#studentForm');
            const guardianRows = $('#guardianRows');
            const table = $('#studentsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.students.data') }}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'admission_no', name: 'admission_no'},
                    {data: 'full_name', name: 'first_name'},
                    {data: 'class_section', name: 'class_section', orderable: false, searchable: false},
                    {data: 'guardian', name: 'guardian', orderable: false, searchable: false},
                    {data: 'status', name: 'status'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ]
            });

            const blankGuardian = {
                id: '',
                name: '',
                relation: '',
                phone: '',
                email: '',
                occupation: '',
                is_primary: true,
                can_pickup: true
            };

            const guardianRowTemplate = (guardian, index, total) => `
                <div class="border rounded p-3 guardian-row bg-body" data-index="${index}">
                    <input type="hidden" name="guardians[${index}][id]" value="${guardian.id ?? ''}">
                    <input type="hidden" class="guardian-primary-value" name="guardians[${index}][is_primary]" value="${guardian.is_primary ? 1 : 0}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label required">Guardian Name</label>
                            <input class="form-control" name="guardians[${index}][name]" value="${guardian.name ?? ''}" required maxlength="150" placeholder="Full name">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label required">Relation</label>
                            <input class="form-control" name="guardians[${index}][relation]" value="${guardian.relation ?? ''}" required maxlength="50" placeholder="Father/Mother">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label required">Phone</label>
                            <input class="form-control" name="guardians[${index}][phone]" value="${guardian.phone ?? ''}" required maxlength="30" placeholder="Phone">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email</label>
                            <input class="form-control" type="email" name="guardians[${index}][email]" value="${guardian.email ?? ''}" maxlength="255" placeholder="Email">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Occupation</label>
                            <input class="form-control" name="guardians[${index}][occupation]" value="${guardian.occupation ?? ''}" maxlength="120" placeholder="Occupation">
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input guardian-primary" type="radio" name="guardian_primary" ${guardian.is_primary ? 'checked' : ''}>
                                <label class="form-check-label">Primary guardian</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <input type="hidden" name="guardians[${index}][can_pickup]" value="0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="guardians[${index}][can_pickup]" value="1" ${guardian.can_pickup ? 'checked' : ''}>
                                <label class="form-check-label">Can pick up student</label>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-guardian" ${total <= 1 ? 'disabled' : ''}>Remove</button>
                        </div>
                    </div>
                </div>
            `;

            const escapeGuardian = (guardian) => {
                const normalized = {...blankGuardian, ...guardian};
                const isPrimary = normalized.is_primary === true || normalized.is_primary === 1 || normalized.is_primary === '1';
                const canPickup = normalized.can_pickup === true || normalized.can_pickup === 1 || normalized.can_pickup === '1';

                return {
                    ...normalized,
                    id: $('<div>').text(normalized.id ?? '').html(),
                    name: $('<div>').text(normalized.name ?? '').html(),
                    relation: $('<div>').text(normalized.relation ?? '').html(),
                    phone: $('<div>').text(normalized.phone ?? '').html(),
                    email: $('<div>').text(normalized.email ?? '').html(),
                    occupation: $('<div>').text(normalized.occupation ?? '').html(),
                    is_primary: isPrimary,
                    can_pickup: canPickup
                };
            };

            const setGuardianRows = (guardians = [blankGuardian]) => {
                const rows = guardians.length ? guardians : [blankGuardian];

                if (!rows.some((guardian) => guardian.is_primary)) {
                    rows[0].is_primary = true;
                }

                guardianRows.html(rows.map((guardian, index) => guardianRowTemplate(escapeGuardian(guardian), index, rows.length)).join(''));
                syncGuardianPrimaryValues();
            };

            const readGuardianRows = () => guardianRows.find('.guardian-row').map(function () {
                const row = $(this);
                return {
                    id: row.find('[name$="[id]"]').val(),
                    name: row.find('[name$="[name]"]').val(),
                    relation: row.find('[name$="[relation]"]').val(),
                    phone: row.find('[name$="[phone]"]').val(),
                    email: row.find('[name$="[email]"]').val(),
                    occupation: row.find('[name$="[occupation]"]').val(),
                    is_primary: row.find('.guardian-primary').is(':checked'),
                    can_pickup: row.find('[name$="[can_pickup]"][type="checkbox"]').is(':checked')
                };
            }).get();

            function syncGuardianPrimaryValues() {
                const primary = guardianRows.find('.guardian-primary:checked').first();

                if (!primary.length) {
                    guardianRows.find('.guardian-primary').first().prop('checked', true);
                }

                guardianRows.find('.guardian-row').each(function () {
                    const row = $(this);
                    row.find('.guardian-primary-value').val(row.find('.guardian-primary').is(':checked') ? 1 : 0);
                });
            }

            // Guardian mode toggle
            const existingParentSection = $('#existingParentSection');
            const newGuardianSection = $('#newGuardianSection');
            const parentSelect = $('#parentSelect');

            $('input[name="guardian_mode"]').on('change', function () {
                if ($(this).val() === 'existing') {
                    existingParentSection.removeClass('d-none');
                    newGuardianSection.addClass('d-none');
                    parentSelect.prop('required', true);
                    // Clear guardian fields so they aren't submitted
                    guardianRows.empty();
                } else {
                    existingParentSection.addClass('d-none');
                    newGuardianSection.removeClass('d-none');
                    parentSelect.prop('required', false);
                    parentSelect.val('');
                    setGuardianRows();
                }
            });

            setGuardianRows();

            $('#createStudent').on('click', () => {
                form[0].reset();
                $('#studentMethod').val('POST');
                form.attr('action', '{{ route('admin.students.store') }}');
                $('#studentOffcanvasTitle').text('Admit Student');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
                
                // Reset select2s
                form.find('select').trigger('change.select2');
                
                guardianRows.empty();
                let guardianCounter = 0;
                $('#modeNew').prop('checked', true).trigger('change');
                
                // Go to first tab
                bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#studentBasic"]')).show();

                offcanvas.show();
            });

            $('#addGuardian').on('click', () => {
                setGuardianRows([...readGuardianRows(), {...blankGuardian, is_primary: false}]);
            });

            guardianRows.on('click', '.remove-guardian', function () {
                const rows = readGuardianRows();
                rows.splice($(this).closest('.guardian-row').index(), 1);
                setGuardianRows(rows);
            });

            guardianRows.on('change', '.guardian-primary', syncGuardianPrimaryValues);

            $('#studentsTable').on('click', '.edit-student', function () {
                $.get($(this).data('url'), (response) => {
                    form[0].reset();
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    form.attr('action', $(this).data('update-url'));
                    $('#studentMethod').val('PUT');
                    $('#studentOffcanvasTitle').text('Edit Student');

                    Object.entries(response.data).forEach(([key, value]) => {
                        if (key === 'guardians') {
                            return;
                        }

                        const field = form.find(`[name="${key}"]`);
                        if (field.length && field.attr('type') !== 'file') {
                            field.val(value);
                        }
                    });

                    // Determine guardian mode based on student data
                    if (response.data.parent_id) {
                        $('#modeExisting').prop('checked', true).trigger('change');
                        parentSelect.val(response.data.parent_id);
                        if (response.data.relationship) {
                            form.find('select[name="relationship"]').val(response.data.relationship);
                        }
                    } else {
                        $('#modeNew').prop('checked', true).trigger('change');
                        setGuardianRows(response.data.guardians);
                    }
                    
                    form.find('select').trigger('change.select2');

                    $('#createUserSwitch').prop('checked', false);

                    offcanvas.show();
                });
            });

            $('#studentsTable').on('click', '.delete-student', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false)
                });
            });

            form.on('erp:success', () => {
                offcanvas.hide();
                table.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush
