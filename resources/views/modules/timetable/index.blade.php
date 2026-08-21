@extends('layouts.admin')

@section('title', 'Timetable')
@section('page-title', 'Timetable Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Timetable</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title fw-semibold mb-0"><i class="ti ti-calendar text-primary me-2"></i>Timetable Slots</h3>
            @can('timetable.create')
                <div class="btn-group ms-auto">
                    <button class="btn btn-primary btn-sm" id="createTimetable">Add Slot</button>
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="visually-hidden">Toggle</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button class="dropdown-item" id="openDuplicateDay">Duplicate Day</button>
                        </li>
                        <li>
                            <button class="dropdown-item" id="openCopyTimetable">Copy Between Classes</button>
                        </li>
                    </ul>
                </div>
            @endcan
        </div>

        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Academic Year</label>
                    <select class="form-select" id="filterAcademicYear">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Class / Section</label>
                    <select class="form-select" id="filterClassSection">
                        <option value="">All</option>
                        @foreach($classSections as $classSection)
                            <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Teacher</label>
                    <select class="form-select" id="filterTeacher">
                        <option value="">All</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Day</label>
                    <select class="form-select" id="filterDay">
                        <option value="">All</option>
                        @foreach($days as $key => $day)
                            <option value="{{ $key }}">{{ $day }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border">
                        <div class="card-header p-3">
                            <h6 class="mb-0"><i class="ti ti-report text-primary me-2"></i>Class Timetable Report</h6>
                        </div>
                        <div class="card-body">
                            <div class="row gy-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label required">Academic Year</label>
                                    <select class="form-select" id="reportClassAcademicYear">
                                        <option value="">Select</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label required">Class / Section</label>
                                    <select class="form-select" id="reportClassSection">
                                        <option value="">Select</option>
                                        @foreach($classSections as $classSection)
                                            <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 text-end d-flex gap-2 justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary" id="previewClassSchedule">Preview</button>
                                    <button type="button" class="btn btn-outline-primary" id="openClassSchedule">Print Class Schedule</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border">
                        <div class="card-header p-3">
                            <h6 class="mb-0"><i class="ti ti-report text-primary me-2"></i>Teacher Timetable Report</h6>
                        </div>
                        <div class="card-body">
                            <div class="row gy-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label required">Academic Year</label>
                                    <select class="form-select" id="reportTeacherAcademicYear">
                                        <option value="">Select</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label required">Teacher</label>
                                    <select class="form-select" id="reportTeacher">
                                        <option value="">Select</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 text-end d-flex gap-2 justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary" id="previewTeacherSchedule">Preview</button>
                                    <button type="button" class="btn btn-outline-primary" id="openTeacherSchedule">Print Teacher Schedule</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-striped table-bordered w-100" id="timetableTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Academic Year</th>
                    <th>Class Section</th>
                    <th>Day</th>
                    <th>Period</th>
                    <th>Time</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Room</th>
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
        id="timetableOffcanvas" 
        formId="timetableForm" 
        title="Add Timetable Slot"
        action="{{ route('admin.timetable.store') }}" 
        method="POST" 
        width="1000px" 
        saveButtonText="Save Slot"
    >
        <input type="hidden" name="_method" value="POST" id="timetableMethod">
        
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Slot Details</h6>
        
        <div class="row g-4">
            <div class="col-md-4">
                <label class="form-label required fw-medium text-dark">Academic Year</label>
                <select class="form-select" name="academic_year_id" required>
                    <option value="">Select</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label required fw-medium text-dark">Class / Section</label>
                <select class="form-select" name="class_section_id" required>
                    <option value="">Select</option>
                    @foreach($classSections as $classSection)
                        <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label required fw-medium text-dark">Teacher</label>
                <select class="form-select" name="teacher_id" required>
                    <option value="">Select</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label required fw-medium text-dark">Subject</label>
                <select class="form-select" name="subject_id" required>
                    <option value="">Select</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label required fw-medium text-dark">Day</label>
                <select class="form-select" name="day_of_week" required>
                    <option value="">Select</option>
                    @foreach($days as $key => $day)
                        <option value="{{ $key }}">{{ $day }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label required fw-medium text-dark">Period #</label>
                <input class="form-control" type="number" name="period_number" required min="1">
            </div>
            <div class="col-md-4">
                <label class="form-label required fw-medium text-dark">Period Label</label>
                <input class="form-control" name="period_label" required maxlength="100">
            </div>
            <div class="col-md-2">
                <label class="form-label required fw-medium text-dark">Start Time</label>
                <input class="form-control" type="time" name="start_time" required>
            </div>
            <div class="col-md-2">
                <label class="form-label required fw-medium text-dark">End Time</label>
                <input class="form-control" type="time" name="end_time" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium text-dark">Room</label>
                <input class="form-control" name="room" maxlength="100">
            </div>
            <div class="col-md-4">
                <label class="form-label required fw-medium text-dark">Status</label>
                <select class="form-select" name="status" required>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected($status === 'active')>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-erp.side-panel>
@endpush

@push('modals')
    <x-erp.side-panel 
        id="duplicateDayOffcanvas" 
        formId="duplicateDayForm" 
        title="Duplicate Day Schedule"
        action="{{ route('admin.timetable.duplicate-day') }}" 
        method="POST" 
        width="500px" 
        saveButtonText="Duplicate"
    >
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Schedule Options</h6>
        <p class="text-muted small">Copy all timetable slots from one day to another for the same class section.</p>
        
        <div class="row g-4">
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Academic Year</label>
                <select class="form-select" name="academic_year_id" id="dupAcademicYear" required>
                    <option value="">Select</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Class / Section</label>
                <select class="form-select" name="class_section_id" id="dupClassSection" required>
                    <option value="">Select</option>
                    @foreach($classSections as $classSection)
                        <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6">
                <label class="form-label required fw-medium text-dark">Source Day</label>
                <select class="form-select" name="source_day" id="dupSourceDay" required>
                    <option value="">Select</option>
                    @foreach($days as $key => $day)
                        <option value="{{ $key }}">{{ $day }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6">
                <label class="form-label required fw-medium text-dark">Target Day</label>
                <select class="form-select" name="target_day" id="dupTargetDay" required>
                    <option value="">Select</option>
                    @foreach($days as $key => $day)
                        <option value="{{ $key }}">{{ $day }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel 
        id="copyTimetableOffcanvas" 
        formId="copyTimetableForm" 
        title="Copy Timetable Between Classes"
        action="{{ route('admin.timetable.copy-class') }}" 
        method="POST" 
        width="500px" 
        saveButtonText="Copy Timetable"
    >
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Copy Options</h6>
        <p class="text-muted small">Copy all timetable slots from one class section to another. Conflicting slots will be skipped.</p>
        
        <div class="row g-4">
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Academic Year</label>
                <select class="form-select" name="academic_year_id" id="copyAcademicYear" required>
                    <option value="">Select</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Source Class / Section</label>
                <select class="form-select" name="source_class_section_id" id="copySourceClass" required>
                    <option value="">Select</option>
                    @foreach($classSections as $classSection)
                        <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Target Class / Section</label>
                <select class="form-select" name="target_class_section_id" id="copyTargetClass" required>
                    <option value="">Select</option>
                    @foreach($classSections as $classSection)
                        <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="adjust_room_names" value="1" id="copyAdjustRoom">
                    <label class="form-check-label" for="copyAdjustRoom">
                        Append "(copied)" to room names to avoid conflicts
                    </label>
                </div>
            </div>
        </div>
    </x-erp.side-panel>

    <div class="modal fade" id="timetablePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="timetablePreviewTitle">Schedule Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="timetablePreviewBody">
                    <div class="text-center py-5 text-muted">
                        <i class="ti ti-loader ti-spin fs-2 mb-3"></i>
                        <p>Preparing schedule preview...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="downloadSchedulePdf" style="display: none;">Download PDF</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const filterAcademicYear = $('#filterAcademicYear');
            const filterClassSection = $('#filterClassSection');
            const filterTeacher = $('#filterTeacher');
            const filterDay = $('#filterDay');
            const timetableOffcanvas = new bootstrap.Offcanvas(document.getElementById('timetableOffcanvas'));
            const timetableForm = $('#timetableForm');
            const duplicateDayOffcanvas = new bootstrap.Offcanvas(document.getElementById('duplicateDayOffcanvas'));
            const copyTimetableOffcanvas = new bootstrap.Offcanvas(document.getElementById('copyTimetableOffcanvas'));

            const timetableTable = $('#timetableTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('admin.timetable.data') }}',
                    data: function (d) {
                        d.academic_year_id = filterAcademicYear.val();
                        d.class_section_id = filterClassSection.val();
                        d.teacher_id = filterTeacher.val();
                        d.day_of_week = filterDay.val();
                    },
                },
                columns: [
                    {data: 'id', name: 'id', visible: false, searchable: false, orderable: false},
                    {data: 'academic_year', name: 'academicYear.name'},
                    {data: 'class_section', name: 'class_section', orderable: false, searchable: false},
                    {data: 'day', name: 'day_of_week'},
                    {data: 'period_number', name: 'period_number'},
                    {data: 'time_range', name: 'start_time', orderable: false, searchable: false},
                    {data: 'subject', name: 'subject.name'},
                    {data: 'teacher', name: 'teacher.first_name'},
                    {data: 'room', name: 'room'},
                    {data: 'status_label', name: 'status', orderable: false, searchable: false},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ],
            });

            [filterAcademicYear, filterClassSection, filterTeacher, filterDay].forEach((control) => {
                control.on('change', () => timetableTable.ajax.reload());
            });

            $('#createTimetable').on('click', () => {
                timetableForm[0].reset();
                $('#timetableMethod').val('POST');
                timetableForm.attr('action', '{{ route('admin.timetable.store') }}');
                $('#timetableOffcanvasTitle').text('Add Timetable Slot');
                timetableForm.find('.is-invalid').removeClass('is-invalid');
                timetableForm.find('.invalid-feedback.dynamic').remove();
                timetableForm.data('is-dirty', false);
                timetableOffcanvas.show();
            });

            $('#openDuplicateDay').on('click', () => {
                $('#duplicateDayForm')[0].reset();
                $('#duplicateDayForm').find('.is-invalid').removeClass('is-invalid');
                $('#duplicateDayForm').find('.invalid-feedback.dynamic').remove();
                $('#duplicateDayForm').data('is-dirty', false);
                duplicateDayOffcanvas.show();
            });

            $('#openCopyTimetable').on('click', () => {
                $('#copyTimetableForm')[0].reset();
                $('#copyTimetableForm').find('.is-invalid').removeClass('is-invalid');
                $('#copyTimetableForm').find('.invalid-feedback.dynamic').remove();
                $('#copyTimetableForm').data('is-dirty', false);
                copyTimetableOffcanvas.show();
            });

            $(document).on('click', '.edit-slot', function () {
                const $btn = $(this);
                const url = $btn.attr('data-url');

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function (response) {
                        timetableForm[0].reset();
                        timetableForm.find('.is-invalid').removeClass('is-invalid');
                        timetableForm.find('.invalid-feedback.dynamic').remove();
                        timetableForm.attr('action', $btn.attr('data-update-url'));
                        $('#timetableMethod').val('PUT');
                        $('#timetableOffcanvasTitle').text('Edit Timetable Slot');

                        Object.entries(response.data).forEach(([key, value]) => {
                            const input = timetableForm.find(`[name="${key}"]`);
                            if (input.length && value != null) {
                                input.val(value);
                            }
                        });

                        timetableForm.data('is-dirty', false);
                        timetableOffcanvas.show();
                    },
                    error: function (xhr) {
                        console.error('[Timetable] Edit failed:', xhr.responseJSON || xhr);
                        App.toast.error('Failed to load timetable slot data.');
                    },
                });
            });

            $(document).on('click', '.delete-slot', function () {
                App.confirmDelete({
                    url: $(this).attr('data-url'),
                    onSuccess: () => timetableTable.ajax.reload(null, false),
                });
            });

            timetableForm.on('erp:success', () => {
                timetableOffcanvas.hide();
                timetableTable.ajax.reload(null, false);
            });

            $('#openClassSchedule').on('click', () => {
                const year = $('#reportClassAcademicYear').val();
                const classSectionId = $('#reportClassSection').val();

                if (!year || !classSectionId) {
                    App.toast.error('Please select an academic year and class/section to print the class schedule.');
                    return;
                }

                const url = new URL('{{ route('admin.timetable.print.class') }}', window.location.origin);
                url.searchParams.set('academic_year_id', year);
                url.searchParams.set('class_section_id', classSectionId);
                window.open(url.toString(), '_blank');
            });

            const timetablePreviewModal = new bootstrap.Modal('#timetablePreviewModal');
            const timetablePreviewTitle = $('#timetablePreviewTitle');
            const timetablePreviewBody = $('#timetablePreviewBody');

            function escapeHtml(value) {
                return $('<div/>').text(value ?? '').html();
            }

            function renderSchedulePreview(title, subtitle, schedule, isTeacher) {
                if (!Array.isArray(schedule) || !schedule.length) {
                    return '<div class="text-center py-5 text-muted"><p class="mb-0">No schedule found for the selected criteria.</p></div>';
                }

                const grouped = schedule.reduce((acc, item) => {
                    if (!acc[item.day_name]) {
                        acc[item.day_name] = [];
                    }
                    acc[item.day_name].push(item);
                    return acc;
                }, {});

                const sortedDays = Object.entries(grouped).sort((a, b) => {
                    return a[1][0].day_of_week - b[1][0].day_of_week;
                });

                let html = '<div class="mb-4"><h5>' + escapeHtml(title) + '</h5>';
                html += '<p class="text-muted mb-0">' + escapeHtml(subtitle) + '</p></div>';

                sortedDays.forEach(([dayName, items]) => {
                    items.sort((a, b) => a.period_number - b.period_number);
                    html += '<div class="mb-4">';
                    html += '<h6 class="mb-3">' + escapeHtml(dayName) + '</h6>';
                    html += '<div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead><tr>';
                    html += '<th>Period</th><th>Time</th><th>Subject</th>';
                    if (isTeacher) {
                        html += '<th>Class Section</th>';
                    }
                    html += '<th>Room</th></tr></thead><tbody>';

                    items.forEach((item) => {
                        html += '<tr>';
                        html += '<td>' + escapeHtml(item.period_label || item.period_number) + '</td>';
                        html += '<td>' + escapeHtml(item.time_range || '') + '</td>';
                        html += '<td>' + escapeHtml(item.subject || '-') + '</td>';
                        if (isTeacher) {
                            html += '<td>' + escapeHtml(item.class_section || '-') + '</td>';
                        }
                        html += '<td>' + escapeHtml(item.room || '-') + '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table></div></div>';
                });

                return html;
            }

            let currentPreviewMode = null;
            let currentPreviewParams = {};

            $('#previewClassSchedule').on('click', () => {
                const year = $('#reportClassAcademicYear').val();
                const classSectionId = $('#reportClassSection').val();
                const yearLabel = $('#reportClassAcademicYear option:selected').text();
                const classLabel = $('#reportClassSection option:selected').text();

                if (!year || !classSectionId) {
                    App.toast.error('Please select an academic year and class/section to preview the class schedule.');
                    return;
                }

                currentPreviewMode = 'class';
                currentPreviewParams = { year, classSectionId };

                timetablePreviewTitle.text('Class Schedule Preview');
                timetablePreviewBody.html('<div class="text-center py-5 text-muted"><i class="ti ti-loader ti-spin fs-2 mb-3"></i><p>Loading class schedule preview...</p></div>');
                $('#downloadSchedulePdf').hide();
                timetablePreviewModal.show();

                $.get('{{ route('admin.timetable.class-schedule') }}', {
                    academic_year_id: year,
                    class_section_id: classSectionId,
                }).done((response) => {
                    timetablePreviewBody.html(renderSchedulePreview(
                        'Class Schedule for ' + classLabel,
                        yearLabel,
                        response.data,
                        false,
                    ));
                    $('#downloadSchedulePdf').show();
                }).fail(() => {
                    timetablePreviewBody.html('<div class="text-center py-5 text-danger"><p class="mb-0">Unable to load class schedule preview.</p></div>');
                });
            });

            $('#previewTeacherSchedule').on('click', () => {
                const year = $('#reportTeacherAcademicYear').val();
                const teacherId = $('#reportTeacher').val();
                const yearLabel = $('#reportTeacherAcademicYear option:selected').text();
                const teacherLabel = $('#reportTeacher option:selected').text();

                if (!year || !teacherId) {
                    App.toast.error('Please select an academic year and teacher to preview the teacher schedule.');
                    return;
                }

                currentPreviewMode = 'teacher';
                currentPreviewParams = { year, teacherId };

                timetablePreviewTitle.text('Teacher Schedule Preview');
                timetablePreviewBody.html('<div class="text-center py-5 text-muted"><i class="ti ti-loader ti-spin fs-2 mb-3"></i><p>Loading teacher schedule preview...</p></div>');
                $('#downloadSchedulePdf').hide();
                timetablePreviewModal.show();

                $.get('{{ route('admin.timetable.teacher-schedule') }}', {
                    academic_year_id: year,
                    teacher_id: teacherId,
                }).done((response) => {
                    timetablePreviewBody.html(renderSchedulePreview(
                        'Teacher Schedule for ' + teacherLabel,
                        yearLabel,
                        response.data,
                        true,
                    ));
                    $('#downloadSchedulePdf').show();
                }).fail(() => {
                    timetablePreviewBody.html('<div class="text-center py-5 text-danger"><p class="mb-0">Unable to load teacher schedule preview.</p></div>');
                });
            });

            $('#downloadSchedulePdf').on('click', () => {
                if (!currentPreviewMode || !currentPreviewParams.year) {
                    App.toast.error('Please preview a schedule first before downloading the PDF.');
                    return;
                }

                let url;
                if (currentPreviewMode === 'class') {
                    url = new URL('{{ route('admin.timetable.print.class') }}', window.location.origin);
                    url.searchParams.set('academic_year_id', currentPreviewParams.year);
                    url.searchParams.set('class_section_id', currentPreviewParams.classSectionId);
                } else {
                    url = new URL('{{ route('admin.timetable.print.teacher') }}', window.location.origin);
                    url.searchParams.set('academic_year_id', currentPreviewParams.year);
                    url.searchParams.set('teacher_id', currentPreviewParams.teacherId);
                }

                window.open(url.toString(), '_blank');
            });

            $('#openTeacherSchedule').on('click', () => {
                const year = $('#reportTeacherAcademicYear').val();
                const teacherId = $('#reportTeacher').val();

                if (!year || !teacherId) {
                    App.toast.error('Please select an academic year and teacher to print the teacher schedule.');
                    return;
                }

                const url = new URL('{{ route('admin.timetable.print.teacher') }}', window.location.origin);
                url.searchParams.set('academic_year_id', year);
                url.searchParams.set('teacher_id', teacherId);
                window.open(url.toString(), '_blank');
            });

            // Reset offcanvas form state when hidden
            $('#timetableOffcanvas').on('hidden.bs.offcanvas', function () {
                timetableForm[0].reset();
                timetableForm.find('.is-invalid').removeClass('is-invalid');
                timetableForm.find('.invalid-feedback.dynamic').remove();
            });

            // === Duplicate Day ===
            const duplicateDayForm = $('#duplicateDayForm');
            duplicateDayForm.on('erp:success', function (e, response) {
                duplicateDayOffcanvas.hide();
                timetableTable.ajax.reload(null, false);
                if (response.data?.errors?.length) {
                    console.warn('Skipped slots:', response.data.errors);
                }
            });

            // === Copy Timetable ===
            const copyTimetableForm = $('#copyTimetableForm');
            copyTimetableForm.on('erp:success', function (e, response) {
                copyTimetableOffcanvas.hide();
                timetableTable.ajax.reload(null, false);
                if (response.data?.errors?.length) {
                    console.warn('Skipped slots:', response.data.errors);
                }
            });

            // Reset offcanvas forms on hidden
            $('#duplicateDayOffcanvas').on('hidden.bs.offcanvas', function () {
                this.querySelector('form').reset();
                $(this).find('.is-invalid').removeClass('is-invalid');
            });

            $('#copyTimetableOffcanvas').on('hidden.bs.offcanvas', function () {
                this.querySelector('form').reset();
                $(this).find('.is-invalid').removeClass('is-invalid');
            });
        })(); });
    </script>
@endpush
