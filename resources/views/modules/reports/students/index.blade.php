@extends("modules.reports.reports_layout")

@section("title", "Student List Report")

@section("content")
    <!-- Filters -->
    <form id="filterForm" class="filter-toolbar mb-3">
        <div class="filter-item">
            <label for="academic_year_id" class="form-label">Academic Year</label>
            <select name="academic_year_id" id="academic_year_id" class="form-select">
                <option value="">All</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-item">
            <label for="class_section_id" class="form-label">Class & Section</label>
            <select name="class_section_id" id="class_section_id" class="form-select">
                <option value="">All</option>
                @foreach($classSections as $cs)
                    <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-item">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
                <option value="">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="filter-item d-flex gap-2 align-items-end pb-1">
            <button type="button" id="filterBtn" class="btn btn-primary"><i class="ti ti-filter me-1"></i> Filter</button>
            <button type="button" id="resetBtn" class="btn btn-outline-secondary"><i class="ti ti-refresh me-1"></i> Reset</button>
        </div>
    </form>

    <!-- Export Buttons -->
    <div class="d-flex gap-2 mb-3">
        <a id="exportExcel" href="{{ route('reports.students.list.export', ['type' => 'excel']) }}" class="btn btn-success btn-sm"><i class="ti ti-file-type-xls me-1"></i> Excel</a>
        <a id="exportPdf" href="{{ route('reports.students.list.export', ['type' => 'pdf']) }}" class="btn btn-danger btn-sm"><i class="ti ti-file-type-pdf me-1"></i> PDF</a>
        <a id="exportPrint" href="{{ route('reports.students.list.export', ['type' => 'print']) }}" class="btn btn-warning btn-sm" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-body p-0">
            <table id="studentsTable" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Admission No</th>
                        <th>Class & Section</th>
                        <th>Guardian</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- DataTables will load data here via AJAX --}}
                </tbody>
            </table>
        </div>
        <div class="card-footer text-center text-muted small">
            Generated on {{ date('Y-m-d H:i:s') }}
        </div>
    </div>
@endsection

@push("scripts")
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', async function () {
        const DataTable = await window.lazyDT();
        var table = $("#studentsTable").DataTable({
            processing: true,
            serverSide: true,
            order: [[1, 'asc']],
            ajax: {
                url: "{{ route("reports.students.list") }}",
                data: function (d) {
                    d.academic_year_id = $('#academic_year_id').val();
                    d.class_section_id = $('#class_section_id').val();
                    d.status = $('#status').val();
                }
            },
            columnDefs: [
                { orderable: false, targets: [0, 5] }
            ],
            columns: [
                {data: "DT_RowIndex", name: "DT_RowIndex"},
                {data: "full_name", name: "full_name"},
                {data: "admission_no", name: "admission_no"},
                {data: "class_section", name: "class_section"},
                {data: "guardian", name: "guardian"},
                {data: "actions", name: "actions", orderable: false, searchable: false},
            ]
        });

        function updateExportLinks() {
            var params = {
                academic_year_id: $('#academic_year_id').val(),
                class_section_id: $('#class_section_id').val(),
                status: $('#status').val()
            };
            var queryString = $.param(params);
            var baseExcel = "{{ route('reports.students.list.export', ['type' => 'excel']) }}";
            var basePdf = "{{ route('reports.students.list.export', ['type' => 'pdf']) }}";
            var basePrint = "{{ route('reports.students.list.export', ['type' => 'print']) }}";

            $('#exportExcel').attr('href', baseExcel + (queryString ? '?' + queryString : ''));
            $('#exportPdf').attr('href', basePdf + (queryString ? '?' + queryString : ''));
            $('#exportPrint').attr('href', basePrint + (queryString ? '?' + queryString : ''));
        }

        $('#filterBtn').on('click', function() {
            table.ajax.reload();
            updateExportLinks();
        });

        $('#resetBtn').on('click', function() {
            $('#academic_year_id').val('');
            $('#class_section_id').val('');
            $('#status').val('');
            table.ajax.reload();
            updateExportLinks();
        });

        updateExportLinks();
    });
</script>
@endpush
