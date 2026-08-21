@extends('layouts.admin')

@section('title', 'Homework')
@section('page-title', 'Homework Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Homework</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title fw-semibold mb-0">
                        <i class="ti ti-books text-primary me-1"></i> Homework List
                    </h3>
                    @can('homework.create')
                        <button class="btn btn-primary btn-sm ms-auto" id="createHomework">Add Homework</button>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Class</label>
                            <select class="form-select" id="filterClass">
                                <option value="">All Classes</option>
                                @foreach ($classSections as $classSection)
                                    <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Filter by Subject</label>
                            <select class="form-select" id="filterSubject">
                                <option value="">All Subjects</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Filter by Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">All Statuses</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <table class="table table-striped table-bordered w-100" id="homeworkTable">
                        <thead>
                        <tr>
                            <th>Title</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Assigned Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th width="140">Actions</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <x-erp.side-panel 
        id="homeworkOffcanvas" 
        formId="homeworkForm" 
        title="Add Homework"
        action="{{ route('admin.homework.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Save Homework"
        :multipart="true"
    >
        <input type="hidden" name="_method" value="POST" id="homeworkMethod">
        
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Homework Details</h6>
        
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Academic Year</label>
                <select class="form-select" name="academic_year_id" id="hwAcademicYear" required>
                    <option value="">Select</option>
                    @foreach ($academicYears as $academicYear)
                        <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Class Section</label>
                <select class="form-select" name="class_section_id" id="hwClassSection" required>
                    <option value="">Select</option>
                    @foreach ($classSections as $classSection)
                        <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Subject</label>
                <select class="form-select" name="subject_id" id="hwSubject" required>
                    <option value="">Select class first</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Status</label>
                <select class="form-select" name="status" required>
                    <option value="">Select</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Homework Title</label>
                <input class="form-control" name="title" required maxlength="255">
            </div>
            <div class="col-12">
                <label class="form-label fw-medium text-dark">Description</label>
                <textarea class="form-control" name="description" rows="4" maxlength="5000"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Assigned Date</label>
                <input class="form-control" type="date" name="assigned_date" required>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Due Date</label>
                <input class="form-control" type="date" name="due_date" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-medium text-dark">Attachment (optional)</label>
                <input class="form-control" type="file" name="attachment" id="hwAttachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip">
                <div class="form-text">Allowed: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, ZIP (max 10MB)</div>
                <div id="currentAttachment" class="mt-2 d-none">
                    <a href="#" target="_blank" class="btn btn-sm btn-outline-primary" id="attachmentLink">
                        <i class="ti ti-download me-1"></i> View Current Attachment
                    </a>
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" name="remove_attachment" value="1" id="removeAttachment">
                        <label class="form-check-label text-danger" for="removeAttachment">Remove attachment</label>
                    </div>
                </div>
            </div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('homeworkOffcanvas'));
            const homeworkForm = $('#homeworkForm');

            const filterClass = $('#filterClass');
            const filterSubject = $('#filterSubject');
            const filterStatus = $('#filterStatus');

            const hwClassSection = $('#hwClassSection');
            const hwSubject = $('#hwSubject');
            const hwAcademicYear = $('#hwAcademicYear');

            const table = $('#homeworkTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('admin.homework.data') }}',
                    data: function (d) {
                        d.class_section_id = filterClass.val();
                        d.subject_id = filterSubject.val();
                        d.status = filterStatus.val();
                    },
                },
                columns: [
                    {data: 'title', name: 'title'},
                    {data: 'class_section', name: 'class_section', orderable: false, searchable: false},
                    {data: 'subject', name: 'subject', orderable: false, searchable: false},
                    {data: 'assigned_date', name: 'assigned_date'},
                    {data: 'due_date', name: 'due_date'},
                    {data: 'status_label', name: 'status', orderable: false, searchable: false},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ],
                order: [[4, 'desc']],
            });

            filterClass.on('change', () => table.ajax.reload());
            filterSubject.on('change', () => table.ajax.reload());
            filterStatus.on('change', () => table.ajax.reload());

            const loadSubjectsByClass = (classSectionId, academicYearId, selectedSubjectId) => {
                hwSubject.html('<option value="">Loading...</option>').prop('disabled', true);

                if (!classSectionId) {
                    hwSubject.html('<option value="">Select class first</option>').prop('disabled', true);
                    return;
                }

                $.get('{{ route('admin.homework.subjects.by-class') }}', {
                    class_section_id: classSectionId,
                    academic_year_id: academicYearId || '',
                }, (response) => {
                    hwSubject.html('<option value="">Select subject</option>');
                    response.data.forEach((subject) => {
                        hwSubject.append(`<option value="${subject.id}" ${subject.id == selectedSubjectId ? 'selected' : ''}>${subject.name}</option>`);
                    });
                    hwSubject.prop('disabled', false);
                }).fail(() => {
                    hwSubject.html('<option value="">No subjects available</option>').prop('disabled', true);
                });
            };

            hwClassSection.on('change', function () {
                loadSubjectsByClass($(this).val(), hwAcademicYear.val(), null);
            });

            hwAcademicYear.on('change', function () {
                if (hwClassSection.val()) {
                    loadSubjectsByClass(hwClassSection.val(), $(this).val(), null);
                }
            });

            $('#createHomework').on('click', () => {
                homeworkForm[0].reset();
                $('#homeworkMethod').val('POST');
                homeworkForm.attr('action', '{{ route('admin.homework.store') }}');
                $('#homeworkOffcanvasTitle').text('Add Homework');
                homeworkForm.find('.is-invalid').removeClass('is-invalid');
                homeworkForm.find('.invalid-feedback.dynamic').remove();
                $('#currentAttachment').addClass('d-none');
                hwSubject.html('<option value="">Select class first</option>').prop('disabled', true);
                
                homeworkFormoffcanvas.show();
            });

            $('#homeworkTable').on('click', '.edit-homework', function () {
                const url = $(this).data('url');
                const updateUrl = $(this).data('update-url');

                $.get(url, (response) => {
                    const data = response.data;
                    homeworkForm[0].reset();
                    homeworkForm.find('.is-invalid').removeClass('is-invalid');
                    homeworkForm.find('.invalid-feedback.dynamic').remove();
                    homeworkForm.attr('action', updateUrl);
                    $('#homeworkMethod').val('POST');
                    $('#homeworkOffcanvasTitle').text('Edit Homework');

                    $('#hwAcademicYear').val(data.academic_year_id);
                    $('#hwClassSection').val(data.class_section_id);
                    homeworkForm.find('[name="title"]').val(data.title);
                    homeworkForm.find('[name="description"]').val(data.description);
                    homeworkForm.find('[name="assigned_date"]').val(data.assigned_date);
                    homeworkForm.find('[name="due_date"]').val(data.due_date);
                    homeworkForm.find('[name="status"]').val(data.status);

                    loadSubjectsByClass(data.class_section_id, data.academic_year_id, data.subject_id);

                    if (data.attachment_url) {
                        $('#currentAttachment').removeClass('d-none');
                        $('#attachmentLink').attr('href', data.attachment_url);
                        $('#removeAttachment').prop('checked', false);
                    } else {
                        $('#currentAttachment').addClass('d-none');
                    }

                    homeworkFormoffcanvas.show();
                });
            });

            $('#homeworkTable').on('click', '.delete-homework', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false),
                });
            });

            homeworkForm.on('erp:success', () => {
                offcanvas.hide();
                table.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush
