@extends('layouts.admin')

@section('title', 'Bulk Result Entry')
@section('page-title', 'Bulk Result Entry — '.$exam->exam_name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
    <li class="breadcrumb-item active">Bulk Entry</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="fw-semibold mb-0">{{ $exam->exam_name }}</h5>
                        <small class="text-muted">
                            {{ $exam->exam_type }} |
                            {{ $exam->classSection?->schoolClass->name }} - {{ $exam->classSection?->section->name }} |
                            {{ $exam->subject?->name }} |
                            Max: {{ $exam->maximum_marks }} | Pass: {{ $exam->pass_marks }} |
                            Date: {{ $exam->exam_date?->format('d M Y') }}
                        </small>
                    </div>
                    <div class="ms-auto d-flex gap-2">
                        <span class="badge bg-{{ $exam->is_published ? 'success' : 'secondary' }} fs-6" id="publishBadge">
                            {{ $exam->is_published ? 'Published' : 'Draft' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                        <div class="input-group input-group-sm" style="max-width:260px">
                            <span class="input-group-text"><i class="ti ti-search"></i></span>
                            <input type="text" class="form-control" id="studentSearch" placeholder="Search students...">
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="hideWithMarks">
                            <label class="form-check-label" for="hideWithMarks">Hide entered</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="showAbsentOnly">
                            <label class="form-check-label" for="showAbsentOnly">Absent only</label>
                        </div>
                        <span class="text-muted small ms-auto" id="resultCount">
                            {{ $students->count() }} students
                        </span>
                    </div>

                    <form id="bulkForm" method="POST" action="{{ route('admin.exams.results.bulk-save', $exam) }}">
                        @csrf
                        <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0" id="bulkTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th width="60">Roll</th>
                                        <th>Student</th>
                                        <th width="120">Marks <small class="text-muted">/{{ $exam->maximum_marks }}</small></th>
                                        <th width="100">Grade</th>
                                        <th width="80">Status</th>
                                        <th>Remarks</th>
                                        <th width="50">Absent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($students as $i => $student)
                                        @php
                                            $existing = $existingResults->get($student->id);
                                        @endphp
                                        <tr class="student-row" data-student-id="{{ $student->id }}">
                                            <td class="text-center">{{ $i + 1 }}</td>
                                            <td class="text-center">{{ $student->roll_no ?? '-' }}</td>
                                            <td>
                                                <span class="fw-medium student-name">{{ $student->full_name }}</span>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm marks-input"
                                                    name="results[{{ $i }}][marks_obtained]"
                                                    value="{{ $existing?->marks_obtained }}"
                                                    min="0" max="{{ $exam->maximum_marks }}"
                                                    data-max="{{ $exam->maximum_marks }}"
                                                    data-pass="{{ $exam->pass_marks }}"
                                                    placeholder="0">
                                                <input type="hidden" name="results[{{ $i }}][student_id]" value="{{ $student->id }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm grade-input"
                                                    name="results[{{ $i }}][grade]"
                                                    value="{{ $existing?->grade }}"
                                                    placeholder="Auto" readonly>
                                            </td>
                                            <td class="text-center">
                                                <span class="status-badge badge bg-secondary">Pending</span>
                                                <input type="hidden" name="results[{{ $i }}][status]" value="{{ $existing?->status ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm remarks-input"
                                                    name="results[{{ $i }}][remarks]"
                                                    value="{{ $existing?->remarks }}"
                                                    placeholder="Remarks">
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check">
                                                    <input class="form-check-input absent-check" type="checkbox"
                                                        name="results[{{ $i }}][absent]"
                                                        value="1"
                                                        data-index="{{ $i }}"
                                                        {{ $existing && $existing->marks_obtained === 0 && $existing->status === 'fail' && !$existing->grade ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                No students found in this class section.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3 justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small" id="saveStatus"></span>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-light" onclick="window.location.href='{{ route('admin.exams.index') }}'">
                                    <i class="ti ti-arrow-left me-1"></i> Back
                                </button>
                                <button type="submit" class="btn btn-primary" id="saveDraftBtn">
                                    <i class="ti ti-device-floppy me-1"></i> Save Draft
                                </button>
                                <button type="button" class="btn btn-success" id="savePublishBtn">
                                    <i class="ti ti-eye me-1"></i> Save &amp; Publish
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const maxMarks = {{ $exam->maximum_marks }};
        const passMarks = {{ $exam->pass_marks }};
        const bulkForm = $('#bulkForm');
        const studentSearch = $('#studentSearch');
        const hideWithMarks = $('#hideWithMarks');
        const showAbsentOnly = $('#showAbsentOnly');
        const saveStatus = $('#saveStatus');

        const calculateGrade = (marks) => {
            if (marks === null || marks === '' || isNaN(marks)) return '';
            const pct = (parseInt(marks) / maxMarks) * 100;
            if (pct >= 90) return 'A+';
            if (pct >= 80) return 'A';
            if (pct >= 70) return 'B+';
            if (pct >= 60) return 'B';
            if (pct >= 50) return 'C';
            if (pct >= 40) return 'D';
            return 'F';
        };

        const getStatus = (marks, grade) => {
            if (marks === null || marks === '' || isNaN(marks)) return 'Pending';
            const m = parseInt(marks);
            if (m === 0 && grade === '') return 'Absent';
            return m >= passMarks ? 'Pass' : 'Fail';
        };

        const updateRow = (row) => {
            const marksInput = row.querySelector('.marks-input');
            const gradeInput = row.querySelector('.grade-input');
            const statusBadge = row.querySelector('.status-badge');
            const statusHidden = row.querySelector('input[name$="[status]"]');
            const absentCheck = row.querySelector('.absent-check');

            const raw = marksInput.value.trim();
            const marks = raw === '' ? null : parseInt(raw);
            const grade = calculateGrade(marks);
            const status = getStatus(marks, grade);

            gradeInput.value = grade;

            let badgeClass = 'bg-secondary';
            let label = 'Pending';
            if (status === 'Pass') { badgeClass = 'bg-success'; label = 'Pass'; }
            else if (status === 'Fail') { badgeClass = 'bg-danger'; label = 'Fail'; }
            else if (status === 'Absent') { badgeClass = 'bg-warning text-dark'; label = 'Absent'; }
            statusBadge.className = `status-badge badge ${badgeClass}`;
            statusBadge.textContent = label;

            if (statusHidden) {
                statusHidden.value = status === 'Pass' ? 'pass' : status === 'Fail' ? 'fail' : 'pending';
            }
        };

        document.querySelectorAll('.student-row').forEach((row) => {
            const marksInput = row.querySelector('.marks-input');
            const gradeInput = row.querySelector('.grade-input');
            const absentCheck = row.querySelector('.absent-check');

            marksInput.addEventListener('input', () => {
                if (absentCheck) absentCheck.checked = false;
                updateRow(row);
            });

            marksInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const rows = [...document.querySelectorAll('.student-row:not(.d-none)')];
                    const idx = rows.indexOf(row);
                    if (idx > -1 && idx < rows.length - 1) {
                        const next = rows[idx + 1].querySelector('.marks-input');
                        if (next) next.focus();
                    }
                }
            });

            if (absentCheck) {
                absentCheck.addEventListener('change', () => {
                    if (absentCheck.checked) {
                        marksInput.value = '0';
                        gradeInput.value = '';
                        updateRow(row);
                    } else {
                        marksInput.value = '';
                        updateRow(row);
                    }
                });
            }

            if (marksInput.value !== '') updateRow(row);
        });

        studentSearch.on('keyup', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.student-row').forEach((row) => {
                const name = row.querySelector('.student-name').textContent.toLowerCase();
                row.classList.toggle('d-none', !name.includes(q));
            });
            updateCount();
        });

        hideWithMarks.on('change', function () {
            document.querySelectorAll('.student-row').forEach((row) => {
                if (!this.checked) { row.classList.remove('d-none'); return; }
                const marks = row.querySelector('.marks-input').value.trim();
                if (marks !== '' && !isNaN(parseInt(marks))) {
                    row.classList.add('d-none');
                }
            });
            studentSearch.trigger('keyup');
        });

        showAbsentOnly.on('change', function () {
            document.querySelectorAll('.student-row').forEach((row) => {
                if (!this.checked) { row.classList.remove('d-none'); return; }
                const marks = row.querySelector('.marks-input').value.trim();
                const isAbsent = marks === '0' && row.querySelector('.grade-input').value === '';
                row.classList.toggle('d-none', !isAbsent);
            });
            studentSearch.trigger('keyup');
        });

        const updateCount = () => {
            const visible = document.querySelectorAll('.student-row:not(.d-none)').length;
            const total = document.querySelectorAll('.student-row').length;
            $('#resultCount').text(`${visible} / ${total} students`);
        };

        const submitForm = (publish = false) => {
            const form = bulkForm[0];
            const formData = new FormData(form);
            if (publish) formData.append('publish', '1');

            const btn = publish ? $('#savePublishBtn') : $('#saveDraftBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
            saveStatus.text('Saving...');

            $.ajax({
                url: form.action,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success(response) {
                    App.toast('success', response.message);
                    saveStatus.text('Saved at ' + new Date().toLocaleTimeString());
                    if (publish) {
                        $('#publishBadge').text('Published').removeClass('bg-secondary').addClass('bg-success');
                    }
                },
                error(xhr) {
                    const msg = xhr.responseJSON?.message || 'Save failed.';
                    App.toast('error', msg);
                    saveStatus.text('Error: ' + msg);
                },
                complete() {
                    btn.prop('disabled', false).html(publish
                        ? '<i class="ti ti-eye me-1"></i> Save & Publish'
                        : '<i class="ti ti-device-floppy me-1"></i> Save Draft');
                },
            });
        };

        $('#saveDraftBtn').on('click', (e) => { e.preventDefault(); submitForm(false); });
        $('#savePublishBtn').on('click', (e) => { e.preventDefault(); submitForm(true); });

        updateCount();
    });
</script>
@endpush