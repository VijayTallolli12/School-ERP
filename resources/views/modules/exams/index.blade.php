@extends('layouts.admin')

@section('title', 'Exams')
@section('page-title', 'Exams & Results')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Exams</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title fw-semibold mb-0"><i class="ti ti-calendar-event text-primary me-2"></i>Exam Schedules</h3>
                    @can('exams.create')
                        <button class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#examModal" id="createExam">
                            <i class="ti ti-plus me-1"></i> Add Exam
                        </button>
                    @endcan
                </div>
                <div class="card-body">
                    <table class="table table-striped table-bordered w-100" id="examsTable">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Academic Year</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Max Marks</th>
                            <th>Status</th>
                            <th>Published</th>
                            <th width="140">Actions</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h3 class="card-title fw-semibold mb-0">
                        <i class="ti ti-clipboard-list text-primary me-1"></i> Exam Results
                    </h3>
                    @can('exams.update')
                        <div class="d-flex align-items-center gap-3 exam-results-toolbar">
                            <a class="btn btn-primary" id="bulkEntryButton" disabled>
                                <i class="ti ti-table me-1"></i> Bulk Entry
                            </a>
                            <button class="btn btn-outline-primary" id="addResultButton" disabled>
                                <i class="ti ti-plus me-1"></i> Add Result
                            </button>
                        </div>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Exam</label>
                            <select class="form-select" id="selectedExam" name="selected_exam_id">
                                <option value="">Choose exam</option>
                                @foreach ($exams as $exam)
                                    <option value="{{ $exam->id }}">{{ $exam->exam_name }} - {{ $exam->exam_date?->format('d M Y') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="selectedExamSummary">
                            <div class="border rounded p-3 bg-body text-secondary">
                                Select an exam to view or add results.
                            </div>
                        </div>
                    </div>

                    <table class="table table-striped table-bordered w-100" id="resultsTable">
                        <thead>
                        <tr>
                            <th>Student</th>
                            <th>Exam</th>
                            <th>Class</th>
                            <th>Marks</th>
                            <th>Grade</th>
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
    <div class="modal fade" id="examModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="examForm" method="POST" action="{{ route('admin.exams.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="examMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="examModalTitle">Add Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Exam Name</label>
                            <input class="form-control" name="exam_name" required maxlength="150">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Type</label>
                            <select class="form-select" name="exam_type" required>
                                <option value="">Select</option>
                                @foreach ($examTypes as $examType)
                                    <option value="{{ $examType }}">{{ $examType }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Academic Year</label>
                            <select class="form-select" name="academic_year_id" required>
                                <option value="">Select</option>
                                @foreach ($academicYears as $academicYear)
                                    <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Class Section</label>
                            <select class="form-select" name="class_section_id" required>
                                <option value="">Select</option>
                                @foreach ($classSections as $classSection)
                                    <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Subject</label>
                            <select class="form-select" name="subject_id" required>
                                <option value="">Select</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Exam Date</label>
                            <input class="form-control" type="date" name="exam_date" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Maximum Marks</label>
                            <input class="form-control" type="number" min="1" name="maximum_marks" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Pass Marks</label>
                            <input class="form-control" type="number" min="0" name="pass_marks" required>
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
                        <div class="col-md-6">
                            <label class="form-label">Publish</label>
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="is_published" value="0">
                                <input class="form-check-input" type="checkbox" name="is_published" value="1" id="examPublishedSwitch">
                                <label class="form-check-label" for="examPublishedSwitch">Publish exam results immediately</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-device-floppy me-1"></i> Save Exam</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="resultForm" method="POST" action="{{ route('admin.exams.results.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="resultMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultModalTitle">Add Exam Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="exam_id" id="resultExamId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Student</label>
                            <select class="form-select" name="student_id" id="resultStudentId" required>
                                <option value="">Select student</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">Marks Obtained</label>
                            <input class="form-control" type="number" min="0" name="marks_obtained" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Grade</label>
                            <input class="form-control" type="text" name="grade" maxlength="50">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-device-floppy me-1"></i> Save Result</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => { (async () => { const DataTable = await window.lazyDT();
            const examModal = new bootstrap.Modal('#examModal');
            const resultModal = new bootstrap.Modal('#resultModal');
            const examForm = $('#examForm');
            const resultForm = $('#resultForm');
            const selectedExam = $('#selectedExam');
            const addResultButton = $('#addResultButton');
            const bulkEntryButton = $('#bulkEntryButton');
            const selectedExamSummary = $('#selectedExamSummary');

            const examsTable = $('#examsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.exams.data') }}',
                columns: [
                    {data: 'exam_name', name: 'exam_name'},
                    {data: 'exam_type', name: 'exam_type'},
                    {data: 'academic_year', name: 'academic_year'},
                    {data: 'class_section', name: 'class_section', orderable: false, searchable: false},
                    {data: 'subject', name: 'subject'},
                    {data: 'exam_date', name: 'exam_date'},
                    {data: 'maximum_marks', name: 'maximum_marks'},
                    {data: 'status_label', name: 'status', orderable: false, searchable: false},
                    {data: 'published', name: 'is_published', orderable: false, searchable: false},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ],
            });

            const resultsTable = $('#resultsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                deferLoading: 0,
                ajax: {
                    url: '{{ route('admin.exams.results.data') }}',
                    data: function (d) {
                        d.exam_id = selectedExam.val();
                    },
                },
                columns: [
                    {data: 'student_name', name: 'student_name'},
                    {data: 'exam_name', name: 'exam_name'},
                    {data: 'class_section', name: 'class_section', orderable: false, searchable: false},
                    {data: 'marks_obtained', name: 'marks_obtained'},
                    {data: 'grade', name: 'grade'},
                    {data: 'status_label', name: 'status', orderable: false, searchable: false},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ],
            });

            const loadExamDetails = (examId) => {
                if (!examId) {
                    addResultButton.prop('disabled', true);
                    bulkEntryButton.prop('disabled', true);
                    selectedExamSummary.html('<div class="border rounded p-3 bg-body text-secondary">Select an exam to view or add results.</div>');
                    resultsTable.clear().draw();
                    return;
                }

                $.get(`{{ url('admin/exams') }}/${examId}`, (response) => {
                    const exam = response.data;
                    selectedExamSummary.html(`
                        <div class="border rounded p-3 bg-body">
                            <strong>Exam:</strong> ${exam.exam_name}<br>
                            <strong>Date:</strong> ${exam.exam_date}<br>
                            <strong>Max Marks:</strong> ${exam.maximum_marks}<br>
                            <strong>Pass Marks:</strong> ${exam.pass_marks}<br>
                            <strong>Status:</strong> ${exam.status}<br>
                            <strong>Published:</strong> ${exam.is_published ? 'Yes' : 'No'}
                        </div>
                    `);
                    addResultButton.prop('disabled', false);
                    bulkEntryButton.prop('disabled', false).attr('href', `{{ url('admin/exams') }}/${exam.id}/results/bulk`);
                    $('#resultExamId').val(exam.id);
                    loadResultStudents(exam.class_section_id);
                    resultsTable.ajax.reload();
                });
            };

            const loadResultStudents = (classSectionId) => {
                resultForm.find('#resultStudentId').html('<option value="">Select student</option>');

                if (!classSectionId) {
                    return;
                }

                $.get(`{{ url('admin/exams/class-sections') }}/${classSectionId}/students`, (response) => {
                    response.data.forEach((student) => {
                        resultForm.find('#resultStudentId').append(`<option value="${student.id}">${student.name}</option>`);
                    });
                });
            };

            $('#createExam').on('click', () => {
                examForm[0].reset();
                $('#examMethod').val('POST');
                examForm.attr('action', '{{ route('admin.exams.store') }}');
                $('#examModalTitle').text('Add Exam');
                examForm.find('.is-invalid').removeClass('is-invalid');
                examForm.find('.invalid-feedback.dynamic').remove();
            });

            $('#examsTable').on('click', '.edit-exam', function () {
                const url = $(this).data('url');
                const updateUrl = $(this).data('update-url');

                $.get(url, (response) => {
                    examForm[0].reset();
                    examForm.find('.is-invalid').removeClass('is-invalid');
                    examForm.find('.invalid-feedback.dynamic').remove();
                    examForm.attr('action', updateUrl);
                    $('#examMethod').val('PUT');
                    $('#examModalTitle').text('Edit Exam');

                    Object.entries(response.data).forEach(([key, value]) => {
                        if (key === 'is_published') return;
                        const input = examForm.find(`[name="${key}"]`);
                        if (input.length) {
                            input.val(value);
                        }
                    });

                    $('#examPublishedSwitch').prop('checked', response.data.is_published);
                    examModal.show();
                });
            });

            $('#examsTable').on('click', '.publish-exam', async function () {
                const url = $(this).data('url');
                const Swal = await window.lazySwal();
                Swal.fire({
                    title: 'Toggle publish status?',
                    text: 'Are you sure you want to change the publish status of this exam?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, toggle it!',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.post(url, {_token: '{{ csrf_token() }}'}, (response) => {
                        App.toast('success', response.message);
                        examsTable.ajax.reload(null, false);
                        if (selectedExam.val()) {
                            loadExamDetails(selectedExam.val());
                        }
                    }).fail((xhr) => {
                        App.toast('error', xhr.responseJSON?.message || 'Toggle failed.');
                    });
                });
            });

            $('#examsTable').on('click', '.delete-exam', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => examsTable.ajax.reload(null, false),
                });
            });

            selectedExam.on('change', function () {
                loadExamDetails($(this).val());
            });

            addResultButton.on('click', () => {
                resultForm[0].reset();
                resultForm.find('.is-invalid').removeClass('is-invalid');
                resultForm.find('.invalid-feedback.dynamic').remove();
                resultForm.attr('action', '{{ route('admin.exams.results.store') }}');
                $('#resultMethod').val('POST');
                $('#resultModalTitle').text('Add Exam Result');
                resultModal.show();
            });

            $('#resultsTable').on('click', '.edit-result', function () {
                $.get($(this).data('url'), (response) => {
                    resultForm[0].reset();
                    resultForm.find('.is-invalid').removeClass('is-invalid');
                    resultForm.find('.invalid-feedback.dynamic').remove();
                    resultForm.attr('action', $(this).data('update-url'));
                    $('#resultMethod').val('PUT');
                    $('#resultModalTitle').text('Edit Exam Result');

                    Object.entries(response.data).forEach(([key, value]) => {
                        const input = resultForm.find(`[name="${key}"]`);
                        if (input.length) {
                            input.val(value);
                        }
                    });

                    resultModal.show();
                });
            });

            $('#resultsTable').on('click', '.delete-result', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => resultsTable.ajax.reload(null, false),
                });
            });

            resultForm.on('erp:success', () => {
                resultModal.hide();
                resultsTable.ajax.reload(null, false);
            });

            examForm.on('erp:success', () => {
                examModal.hide();
                examsTable.ajax.reload(null, false);
                selectedExamSummary.html('');
                selectedExam.val('');
                addResultButton.prop('disabled', true);
                resultsTable.ajax.reload();
            });
        });
    </script>
@endpush
