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
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h3 class="card-title fw-semibold mb-0">
                        <i class="ti ti-books text-primary me-1"></i> Homework List
                    </h3>
                    @can('homework.create')
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#homeworkModal" id="createHomework">
                                <i class="ti ti-plus me-1"></i> Add Homework
                            </button>
                        </div>
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
    <div class="modal fade" id="homeworkModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="homeworkForm" method="POST" action="{{ route('admin.homework.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="POST" id="homeworkMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="homeworkModalTitle">Add Homework</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Academic Year</label>
                            <select class="form-select" name="academic_year_id" id="hwAcademicYear" required>
                                <option value="">Select</option>
                                @foreach ($academicYears as $academicYear)
                                    <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Class Section</label>
                            <select class="form-select" name="class_section_id" id="hwClassSection" required>
                                <option value="">Select</option>
                                @foreach ($classSections as $classSection)
                                    <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Subject</label>
                            <select class="form-select" name="subject_id" id="hwSubject" required>
                                <option value="">Select class first</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="">Select</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label required">Homework Title</label>
                            <input class="form-control" name="title" required maxlength="255">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4" maxlength="5000"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Assigned Date</label>
                            <input class="form-control" type="date" name="assigned_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Due Date</label>
                            <input class="form-control" type="date" name="due_date" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Attachment (optional)</label>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-device-floppy me-1"></i> Save Homework</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const homeworkModal = new bootstrap.Modal('#homeworkModal');
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
                $('#homeworkModalTitle').text('Add Homework');
                homeworkForm.find('.is-invalid').removeClass('is-invalid');
                homeworkForm.find('.invalid-feedback.dynamic').remove();
                $('#currentAttachment').addClass('d-none');
                hwSubject.html('<option value="">Select class first</option>').prop('disabled', true);
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
                    $('#homeworkModalTitle').text('Edit Homework');

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

                    homeworkModal.show();
                });
            });

            $('#homeworkTable').on('click', '.delete-homework', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false),
                });
            });

            homeworkForm.on('erp:success', () => {
                homeworkModal.hide();
                table.ajax.reload(null, false);
            });
        });
    </script>
@endpush
