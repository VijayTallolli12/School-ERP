@extends('layouts.admin')

@section('title', 'Attendance')
@section('page-title', 'Attendance Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Attendance</li>
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
            <h3 class="card-title mb-0"><i class="ti ti-filter text-primary me-2"></i>Filters</h3>
            <div class="ms-auto d-flex flex-wrap gap-2">
                @can('attendance.create')
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#attendanceModal" id="btnOpenMark">
                        <i class="ti ti-plus me-1"></i> Mark Attendance
                    </button>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#bulkMarkModal" id="btnOpenBulk">
                        <i class="ti ti-users me-1"></i> Bulk Mark
                    </button>
                @endcan
                @can('attendance.view')
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#monthlyReportModal">
                        <i class="ti ti-calendar-days me-1"></i> Monthly Summary
                    </button>
                @endcan
                @can('attendance.reports')
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-success" id="btnExportExcel" title="Export Excel">
                            <i class="ti ti-file-type-xls me-1"></i> Excel
                        </button>
                        <button type="button" class="btn btn-outline-danger" id="btnExportPdf" title="Export PDF">
                            <i class="ti ti-file-type-pdf me-1"></i> PDF
                        </button>
                        <button type="button" class="btn btn-outline-info" id="btnPrint" title="Print">
                            <i class="ti ti-printer me-1"></i> Print
                        </button>
                    </div>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="filterClassSection" class="form-label">Class Section</label>
                    <select id="filterClassSection" class="form-select form-select-sm">
                        <option value="">All class sections</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filterAcademicYear" class="form-label">Academic Year</label>
                    <select id="filterAcademicYear" class="form-select form-select-sm">
                        <option value="">All years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filterFromDate" class="form-label">From Date</label>
                    <input type="date" id="filterFromDate" class="form-control form-control-sm" value="{{ now()->subMonth()->toDateString() }}">
                </div>
                <div class="col-md-2">
                    <label for="filterToDate" class="form-label">To Date</label>
                    <input type="date" id="filterToDate" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                </div>
                <div class="col-md-2">
                    <label for="filterStatus" class="form-label">Status</label>
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">All statuses</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end gap-1">
                    <button type="button" class="btn btn-primary btn-sm w-100" id="btnApplyFilters"><i class="ti ti-check me-1"></i>Apply</button>
                </div>
            </div>
            <div class="mt-2">
                <button type="button" class="btn btn-link btn-sm px-0" id="btnResetFilters"><i class="ti ti-refresh me-1"></i>Reset filters</button>
            </div>
        </div>
    </div>

    <div class="card mb-3 d-none" id="statsCard">
        <div class="card-header">
            <h3 class="card-title fw-semibold mb-0"><i class="ti ti-chart-bar text-primary me-2"></i>Statistics <small class="text-muted fw-normal">(current filters)</small></h3>
        </div>
        <div class="card-body">
            <div class="row g-3 text-center" id="statsRow"></div>
            <div class="table-responsive mt-3">
                <table class="table table-sm table-bordered mb-0" id="statsByClassTable">
                    <thead>
                        <tr>
                            <th>Class Section</th>
                            <th>Total</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                            <th>Half Day</th>
                            <th>Excused</th>
                            <th>Present %</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title fw-semibold mb-0"><i class="ti ti-table text-primary me-2"></i>Attendance Register</h3>
        </div>
        <div class="card-body">
            <table id="attendanceTable" class="table table-striped table-bordered w-100">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Roll No</th>
                        <th>Class Section</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Marked By</th>
                        <th>Remarks</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="markAttendanceForm" method="POST" action="{{ route('admin.attendance.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="markMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="markModalTitle">Mark Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="attendanceClassSectionId" class="form-label">Class Section <span class="text-danger">*</span></label>
                        <select id="attendanceClassSectionId" name="class_section_id" class="form-select" required>
                            <option value="">Select class section</option>
                            @foreach($classSections as $cs)
                                <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="attendanceStudentId" class="form-label">Student <span class="text-danger">*</span></label>
                        <select id="attendanceStudentId" name="student_id" class="form-select searchable-select" required disabled data-placeholder="Select class section first">
                            <option value="">Select class section first</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="attendanceAcademicYearId" class="form-label">Academic Year <span class="text-danger">*</span></label>
                        <select id="attendanceAcademicYearId" name="academic_year_id" class="form-select" required>
                            <option value="">Select academic year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" @selected((($year->status ?? '') === 'active') || $year->is_active)>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="attendanceDate" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" id="attendanceDate" name="attendance_date" class="form-control" required value="{{ now()->toDateString() }}">
                    </div>
                    <div class="mb-3">
                        <label for="attendanceStatus" class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="attendanceStatus" name="status" class="form-select" required>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected($key === 'present')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="attendanceRemarks" class="form-label">Remarks</label>
                        <textarea id="attendanceRemarks" name="remarks" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ti ti-x me-1"></i>Cancel</button>
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-device-floppy me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="bulkMarkModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="bulkMarkAttendanceForm" method="POST" action="{{ route('admin.attendance.bulk-mark') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Mark Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="bulkClassSectionId" class="form-label">Class Section <span class="text-danger">*</span></label>
                            <select id="bulkClassSectionId" name="class_section_id" class="form-select" required>
                                <option value="">Select class section</option>
                                @foreach($classSections as $cs)
                                    <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="bulkAttendanceDate" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" id="bulkAttendanceDate" name="attendance_date" class="form-control" required value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-3">
                            <label for="bulkAcademicYearId" class="form-label">Academic Year <span class="text-danger">*</span></label>
                            <select id="bulkAcademicYearId" name="academic_year_id" class="form-select" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" @selected((($year->status ?? '') === 'active') || $year->is_active)>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Students</label>
                        <div id="bulkStudentsList" class="border rounded p-3 bg-body" style="max-height: 420px; overflow-y: auto;">
                            <p class="text-muted text-center mb-0">Select a class section to load students.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ti ti-x me-1"></i>Cancel</button>
                    <button type="submit" class="btn btn-success py-2"><i class="ti ti-device-floppy me-1"></i> Save attendance</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="monthlyReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Monthly Attendance Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="monthlyClassSectionId" class="form-label">Class Section</label>
                            <select id="monthlyClassSectionId" class="form-select">
                                <option value="">Select</option>
                                @foreach($classSections as $cs)
                                    <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="monthlyMonth" class="form-label">Month</label>
                            <select id="monthlyMonth" class="form-select">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected((int) now()->month === $m)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="monthlyYear" class="form-label">Year</label>
                            <input type="number" class="form-control" id="monthlyYear" value="{{ now()->year }}" min="2000" max="2100">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" id="btnLoadMonthly"><i class="ti ti-download me-1"></i>Load</button>
                        </div>
                    </div>
                    <div id="monthlySummaryBadges" class="mb-3"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="monthlyStudentTable">
                            <thead>
                                <tr>
                                    <th>Roll</th>
                                    <th>Student</th>
                                    @foreach($statuses as $key => $label)
                                        <th class="text-center">{{ $label }}</th>
                                    @endforeach
                                    <th class="text-center">Days marked</th>
                                </tr>
                            </thead>
                            <tbody id="monthlyStudentTbody">
                                <tr><td colspan="{{ 3 + count($statuses) }}" class="text-muted text-center">Choose class section and load.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const routes = {
                data: @json(route('admin.attendance.data')),
                store: @json(route('admin.attendance.store')),
                statistics: @json(route('admin.attendance.statistics')),
                exportExcel: @json(route('admin.attendance.export.excel')),
                exportPdf: @json(route('admin.attendance.export.pdf')),
                print: @json(route('admin.attendance.print')),
            };

            const attendanceItemUrl = (id) => @json(url('admin/attendance')) + '/' + id;

            let currentFilters = {};
            let attendanceTable;

            function readFiltersFromInputs() {
                return {
                    class_section_id: document.getElementById('filterClassSection').value,
                    academic_year_id: document.getElementById('filterAcademicYear').value,
                    from_date: document.getElementById('filterFromDate').value,
                    to_date: document.getElementById('filterToDate').value,
                    status: document.getElementById('filterStatus').value,
                };
            }

            function filterQueryString() {
                const p = new URLSearchParams();
                Object.entries(currentFilters).forEach(([k, v]) => {
                    if (v !== '' && v !== null && v !== undefined) {
                        p.append('filters[' + k + ']', v);
                    }
                });
                return p.toString();
            }

            function reportExportQueryString() {
                const p = new URLSearchParams();
                Object.entries(currentFilters).forEach(([k, v]) => {
                    if (v !== '' && v !== null && v !== undefined) {
                        p.append(k, v);
                    }
                });
                return p.toString();
            }

            function studentsUrl(classSectionId) {
                return @json(url('admin/attendance/class-sections')) + '/' + classSectionId + '/students';
            }

            function monthlyUrl(classSectionId) {
                return @json(url('admin/attendance/class-sections')) + '/' + classSectionId + '/monthly-report';
            }

            currentFilters = readFiltersFromInputs();

            attendanceTable = $('#attendanceTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: routes.data,
                    data: function (d) {
                        d.filters = currentFilters;
                    }
                },
                columns: [
                    { data: 'student_name', name: 'student_name', orderable: false, searchable: true },
                    { data: 'roll_no', name: 'roll_no', orderable: false, searchable: false },
                    { data: 'class_section', name: 'class_section', orderable: false, searchable: true },
                    { data: 'attendance_date', name: 'attendance_date' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'marked_by', name: 'marked_by', orderable: false, searchable: false },
                    { data: 'remarks', name: 'remarks', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
                order: [[3, 'desc']],
                pageLength: 25,
            });

            function refreshStatistics() {
                const qs = filterQueryString();
                fetch(routes.statistics + (qs ? '?' + qs : ''), { headers: { Accept: 'application/json' } })
                    .then((r) => r.json())
                    .then((json) => {
                        if (!json.success || !json.data) return;
                        const d = json.data;
                        const card = document.getElementById('statsCard');
                        const row = document.getElementById('statsRow');
                        const tbody = document.querySelector('#statsByClassTable tbody');
                        card.classList.remove('d-none');
                        const labels = {
                            present: 'Present',
                            absent: 'Absent',
                            late: 'Late',
                            half_day: 'Half Day',
                            excused: 'Excused',
                        };
                        row.innerHTML = '';
                        Object.entries(labels).forEach(([key, label]) => {
                            const col = document.createElement('div');
                            col.className = 'col-6 col-md-2';
                            col.innerHTML = '<div class="border rounded p-2 bg-body"><div class="small text-muted">' + label + '</div><div class="fs-5 fw-semibold">' + (d.totals[key] || 0) + '</div></div>';
                            row.appendChild(col);
                        });
                        const rateCol = document.createElement('div');
                        rateCol.className = 'col-12 col-md-2';
                        rateCol.innerHTML = '<div class="border rounded p-2 bg-primary-subtle"><div class="small text-muted">Engagement %</div><div class="fs-5 fw-semibold">' + d.attendance_rate + '%</div><div class="small text-muted">Present+Late+Half+Excused / Total</div></div>';
                        row.appendChild(rateCol);

                        tbody.innerHTML = '';
                        (d.by_class || []).forEach((line) => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = '<td>' + line.label + '</td><td>' + line.total + '</td><td>' + line.present + '</td><td>' + line.absent + '</td><td>' + line.late + '</td><td>' + (line.half_day || 0) + '</td><td>' + (line.excused || 0) + '</td><td>' + line.rate + '%</td>';
                            tbody.appendChild(tr);
                        });
                    })
                    .catch(() => {});
            }

            function setMarkFieldsDisabled(disabled) {
                ['attendanceClassSectionId', 'attendanceStudentId', 'attendanceAcademicYearId', 'attendanceDate'].forEach((id) => {
                    const el = document.getElementById(id);
                    if (el) el.disabled = disabled;
                });
            }

            function resetMarkForm() {
                const form = document.getElementById('markAttendanceForm');
                form.reset();
                document.getElementById('markMethod').value = 'POST';
                $(form).attr('action', routes.store);
                document.getElementById('markModalTitle').textContent = 'Mark Attendance';
                document.getElementById('attendanceDate').value = new Date().toISOString().split('T')[0];
                document.getElementById('attendanceStatus').value = 'present';
                const $as = $('#attendanceStudentId');
                if ($as.data('select2')) $as.select2('destroy');
                $as[0].innerHTML = '<option value="">Select class section first</option>';
                $as.prop('disabled', true);
                setMarkFieldsDisabled(false);
                $(form).find('.is-invalid').removeClass('is-invalid');
                $(form).find('.invalid-feedback.dynamic').remove();
            }

            function resetBulkMarkForm() {
                const form = document.getElementById('bulkMarkAttendanceForm');
                form.reset();
                document.getElementById('bulkAttendanceDate').value = new Date().toISOString().split('T')[0];
                document.getElementById('bulkStudentsList').innerHTML = '<p class="text-muted text-center mb-0">Select a class section to load students.</p>';
                $(form).find('.is-invalid').removeClass('is-invalid');
                $(form).find('.invalid-feedback.dynamic').remove();
            }

            function loadMarkStudents(classSectionId, afterLoad) {
                const $sel = $('#attendanceStudentId');
                // Destroy Select2 before DOM manipulation
                if ($sel.data('select2')) $sel.select2('destroy');
                const sel = $sel[0];
                if (!classSectionId) {
                    sel.innerHTML = '<option value="">Select class section first</option>';
                    sel.disabled = true;
                    afterLoad?.();
                    return;
                }
                fetch(studentsUrl(classSectionId), { headers: { Accept: 'application/json' } })
                    .then((r) => r.json())
                    .then((data) => {
                        sel.innerHTML = '<option value="">Select student</option>';
                        if (data.success && data.data && data.data.length) {
                            data.data.forEach((s) => {
                                const opt = document.createElement('option');
                                opt.value = s.id;
                                opt.textContent = s.name + (s.roll_no ? ' (' + s.roll_no + ')' : '');
                                sel.appendChild(opt);
                            });
                            sel.disabled = false;
                        } else {
                            sel.innerHTML = '<option value="">No students in this class</option>';
                            sel.disabled = true;
                        }
                        // Re-init Select2
                        App.initSearchableSelects($sel.parent());
                        afterLoad?.();
                    })
                    .catch(() => {
                        sel.innerHTML = '<option value="">Failed to load</option>';
                        sel.disabled = true;
                    });
            }

            function loadBulkStudents(classSectionId) {
                const box = document.getElementById('bulkStudentsList');
                if (!classSectionId) {
                    box.innerHTML = '<p class="text-muted text-center mb-0">Select a class section to load students.</p>';
                    return;
                }
                fetch(studentsUrl(classSectionId), { headers: { Accept: 'application/json' } })
                    .then((r) => r.json())
                    .then((data) => {
                        if (!data.success || !data.data || !data.data.length) {
                            box.innerHTML = '<p class="text-muted text-center mb-0">No students found.</p>';
                            return;
                        }
                        let html = '<table class="table table-sm mb-0"><thead><tr><th>Name</th><th>Roll</th><th>Status</th><th>Remarks</th></tr></thead><tbody>';
                        data.data.forEach((student) => {
                            html += '<tr><td>' + student.name + '</td><td>' + student.roll_no + '</td><td>';
                            html += '<select name="students[' + student.id + ']" class="form-select form-select-sm" required>';
                            html += '<option value="present">Present</option><option value="absent">Absent</option><option value="late">Late</option>';
                            html += '<option value="half_day">Half Day</option><option value="excused">Excused</option></select></td><td>';
                            html += '<input type="text" name="remarks[' + student.id + ']" class="form-control form-control-sm" maxlength="500" placeholder="Optional"></td></tr>';
                        });
                        html += '</tbody></table>';
                        box.innerHTML = html;
                    })
                    .catch(() => {
                        box.innerHTML = '<div class="alert alert-danger mb-0">Error loading students.</div>';
                    });
            }

            window.editAttendance = function (attendanceId) {
                fetch(attendanceItemUrl(attendanceId), { headers: { Accept: 'application/json' } })
                    .then((r) => r.json())
                    .then((data) => {
                        if (!data.success) return;
                        const d = data.data;
                        const form = document.getElementById('markAttendanceForm');
                        document.getElementById('markModalTitle').textContent = 'Edit Attendance';
                        document.getElementById('markMethod').value = 'PUT';
                        $(form).attr('action', attendanceItemUrl(attendanceId));
                        document.getElementById('attendanceClassSectionId').value = d.class_section_id;
                        loadMarkStudents(d.class_section_id, () => {
                            $('#attendanceStudentId').val(String(d.student_id)).trigger('change');
                            document.getElementById('attendanceAcademicYearId').value = String(d.academic_year_id);
                            document.getElementById('attendanceDate').value = d.attendance_date;
                            document.getElementById('attendanceStatus').value = d.status;
                            document.getElementById('attendanceRemarks').value = d.remarks || '';
                            setMarkFieldsDisabled(true);
                            document.getElementById('attendanceStatus').disabled = false;
                            document.getElementById('attendanceRemarks').disabled = false;
                            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('attendanceModal'));
                            modal.show();
                        });
                    });
            };

            $('#btnApplyFilters').on('click', () => {
                currentFilters = readFiltersFromInputs();
                attendanceTable.ajax.reload(null, false);
                refreshStatistics();
            });

            $('#btnResetFilters').on('click', () => {
                document.getElementById('filterClassSection').value = '';
                document.getElementById('filterAcademicYear').value = '';
                document.getElementById('filterFromDate').value = @json(now()->subMonth()->toDateString());
                document.getElementById('filterToDate').value = @json(now()->toDateString());
                document.getElementById('filterStatus').value = '';
                currentFilters = readFiltersFromInputs();
                attendanceTable.ajax.reload(null, false);
                refreshStatistics();
            });

            $('#markAttendanceForm, #bulkMarkAttendanceForm').on('erp:success', function () {
                bootstrap.Modal.getInstance(document.getElementById('attendanceModal'))?.hide();
                bootstrap.Modal.getInstance(document.getElementById('bulkMarkModal'))?.hide();
                attendanceTable.ajax.reload(null, false);
                refreshStatistics();
            });

            $('#attendanceTable').on('click', '.delete-attendance', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => {
                        attendanceTable.ajax.reload(null, false);
                        refreshStatistics();
                    },
                });
            });

            $('#btnOpenMark').on('click', () => resetMarkForm());
            $('#btnOpenBulk').on('click', () => resetBulkMarkForm());

            $('#attendanceClassSectionId').on('change', function () {
                loadMarkStudents($(this).val());
            });

            $('#bulkClassSectionId').on('change', function () {
                loadBulkStudents($(this).val());
            });

            $('#btnLoadMonthly').on('click', () => {
                const cs = document.getElementById('monthlyClassSectionId').value;
                if (!cs) {
                    App.toast('warning', 'Select a class section.');
                    return;
                }
                const month = document.getElementById('monthlyMonth').value;
                const year = document.getElementById('monthlyYear').value;
                const url = monthlyUrl(cs) + '?month=' + encodeURIComponent(month) + '&year=' + encodeURIComponent(year);
                fetch(url, { headers: { Accept: 'application/json' } })
                    .then((r) => r.json())
                    .then((json) => {
                        if (!json.success || !json.data) return;
                        const d = json.data;
                        const statusKeys = @json(array_keys($statuses));
                        const statusLabels = @json($statuses);
                        let badges = '';
                        statusKeys.forEach((k) => {
                            badges += '<span class="badge bg-secondary me-1">' + (statusLabels[k] || k) + ': ' + (d.summary[k] || 0) + '</span>';
                        });
                        document.getElementById('monthlySummaryBadges').innerHTML = '<div class="fw-semibold mb-1">' + d.class_section + '</div>' + badges;
                        const tbody = document.getElementById('monthlyStudentTbody');
                        tbody.innerHTML = '';
                        (d.students || []).forEach((row) => {
                            const tr = document.createElement('tr');
                            let cells = '<td>' + (row.roll_no ?? '-') + '</td><td>' + row.name + '</td>';
                            statusKeys.forEach((k) => {
                                cells += '<td class="text-center">' + (row.counts[k] || 0) + '</td>';
                            });
                            cells += '<td class="text-center">' + row.total_marked + '</td>';
                            tr.innerHTML = cells;
                            tbody.appendChild(tr);
                        });
                    })
                    .catch(() => toastr.error('Could not load monthly report.'));
            });

            @can('attendance.reports')
            $('#btnExportExcel').on('click', () => {
                const qs = reportExportQueryString();
                window.location = routes.exportExcel + (qs ? '?' + qs : '');
            });
            $('#btnExportPdf').on('click', () => {
                const qs = reportExportQueryString();
                window.location = routes.exportPdf + (qs ? '?' + qs : '');
            });
            $('#btnPrint').on('click', () => {
                const qs = reportExportQueryString();
                window.open(routes.print + (qs ? '?' + qs : ''), '_blank', 'noopener');
            });
            @endcan

            refreshStatistics();
        })(); });
    </script>
@endpush
