@extends('layouts.admin')

@section("title", "Student Directory")
@section("page-title", "Student Directory")

@push('styles')
<style>
    .stat-card-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
    .student-photo-sm { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; }
    .student-initials { width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 600; font-size: 14px; }
</style>
@endpush

@section("content")
    <div class="mb-3">
        <a href="{{ route('reports.students.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to Student Reports
        </a>
    </div>

    {{-- Filter Card --}}
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Class & Section</label>
                    <select name="class_section_id" id="class_section_id" class="form-select">
                        <option value="">All</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Gender</label>
                    <select name="gender" id="gender" class="form-select">
                        <option value="">All</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Admission</label>
                    <input type="date" name="start_date" id="start_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Admission</label>
                    <input type="date" name="end_date" id="end_date" class="form-control">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="button" id="filterBtn" class="btn btn-primary py-2">
                        <i class="ti ti-filter me-1"></i> Filter
                    </button>
                    <button type="button" id="resetBtn" class="btn btn-outline-secondary py-2">
                        <i class="ti ti-refresh me-1"></i> Reset
                    </button>
                    <a id="exportExcel" href="{{ route('reports.students.directory.export', ['type' => 'excel']) }}" class="btn btn-success py-2">
                        <i class="ti ti-file-type-xls me-1"></i> Excel
                    </a>
                    <a id="exportPdf" href="{{ route('reports.students.directory.export', ['type' => 'pdf']) }}" class="btn btn-danger py-2">
                        <i class="ti ti-file-type-pdf me-1"></i> PDF
                    </a>
                    <a id="exportPrint" href="{{ route('reports.students.directory.export', ['type' => 'print']) }}" class="btn btn-warning py-2" target="_blank">
                        <i class="ti ti-printer me-1"></i> Print
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4" id="summaryCards">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-primary bg-opacity-10">
                        <i class="ti ti-users text-primary fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Total Students</p>
                        <h3 class="fw-bold mb-0" id="totalStudents">0</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-success bg-opacity-10">
                        <i class="ti ti-user-check text-success fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Active Students</p>
                        <h3 class="fw-bold text-success mb-0" id="activeStudents">0</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-danger border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-danger bg-opacity-10">
                        <i class="ti ti-user-x text-danger fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Inactive Students</p>
                        <h3 class="fw-bold text-danger mb-0" id="inactiveStudents">0</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-info border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-info bg-opacity-10">
                        <i class="ti ti-user-plus text-info fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">New Admissions</p>
                        <h3 class="fw-bold text-info mb-0" id="newAdmissions">0</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Directory DataTable --}}
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Student Directory</h5>
            <span class="text-muted fs-13" id="recordCount">0 records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="directoryTable">
                    <thead>
                        <tr>
                            <th style="width:50px;">Photo</th>
                            <th>Admission No</th>
                            <th>Student Name</th>
                            <th>Class & Section</th>
                            <th>Gender</th>
                            <th>Date of Birth</th>
                            <th>Parent Name</th>
                            <th>Parent Mobile</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th style="width:70px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var table = $('#directoryTable').DataTable({
            processing: true,
            serverSide: true,
            order: [[2, 'asc']],
            ajax: {
                url: "{{ route('reports.students.directory') }}",
                data: function(d) {
                    d.academic_year_id = $('#academic_year_id').val();
                    d.class_section_id = $('#class_section_id').val();
                    d.status = $('#status').val();
                    d.gender = $('#gender').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [
                {data: 'photo', name: 'photo', orderable: false, searchable: false},
                {data: 'admission_no', name: 'admission_no'},
                {data: 'student_name', name: 'student_name'},
                {data: 'class_section', name: 'class_section'},
                {data: 'gender', name: 'gender'},
                {data: 'date_of_birth', name: 'date_of_birth'},
                {data: 'parent_name', name: 'parent_name'},
                {data: 'parent_mobile', name: 'parent_mobile'},
                {data: 'email', name: 'email'},
                {data: 'status_badge', name: 'status', orderable: false, searchable: false},
                {data: 'actions', name: 'actions', orderable: false, searchable: false},
            ],
            columnDefs: [
                { targets: [0, 10], orderable: false, searchable: false }
            ],
            pageLength: 25,
            drawCallback: function() {
                var api = this.api();
                var count = api.rows({filter: 'applied'}).count();
                var total = api.rows().count();
                var active = api.column(9).data().filter(function(v) { return v.indexOf('Active') > -1; }).length;
                var inactive = total - active;
                var filteredCount = api.rows({filter: 'applied'}).count();

                $('#totalStudents').text(total);
                $('#activeStudents').text(active);
                $('#inactiveStudents').text(inactive);
                $('#newAdmissions').text('--');
                $('#recordCount').text(filteredCount + ' records');
            }
        });

        function updateExportLinks() {
            var params = {
                academic_year_id: $('#academic_year_id').val(),
                class_section_id: $('#class_section_id').val(),
                status: $('#status').val(),
                gender: $('#gender').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
            };
            var qs = $.param(params);
            $('#exportExcel').attr('href', "{{ route('reports.students.directory.export', ['type' => 'excel']) }}" + (qs ? '?' + qs : ''));
            $('#exportPdf').attr('href', "{{ route('reports.students.directory.export', ['type' => 'pdf']) }}" + (qs ? '?' + qs : ''));
            $('#exportPrint').attr('href', "{{ route('reports.students.directory.export', ['type' => 'print']) }}" + (qs ? '?' + qs : ''));
        }

        $('#filterBtn').on('click', function() {
            table.ajax.reload();
            updateExportLinks();
        });

        $('#resetBtn').on('click', function() {
            $('#filterForm')[0].reset();
            table.ajax.reload();
            updateExportLinks();
        });

        updateExportLinks();
    });
</script>
@endpush
