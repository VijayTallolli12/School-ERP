@extends("Reports::reports_layout")

@section("title", "Student Directory Report")
@section("report_title", "Student Directory Report")

@section("content")
    <div class="row mb-3">
        <div class="col-md-12">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="me-3">
                    <label for="academic_year_id" class="form-label me-2">Academic Year:</label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="me-3">
                    <label for="class_section_id" class="form-label me-2">Class & Section:</label>
                    <select name="class_section_id" id="class_section_id" class="form-select">
                        <option value="">All</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="me-3">
                    <label for="status" class="form-label me-2">Status:</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="button" id="filterBtn" class="btn btn-primary"><i class="ti ti-filter me-1"></i> Filter</button>
                <button type="button" id="resetBtn" class="btn btn-secondary ms-2"><i class="ti ti-refresh me-1"></i> Reset</button>
                <a id="exportExcel" href="{{ route('reports.students.directory.export', ['type' => 'excel']) }}" class="btn btn-success ms-2"><i class="ti ti-file-spreadsheet me-1"></i> Export Excel</a>
                <a id="exportPdf" href="{{ route('reports.students.directory.export', ['type' => 'pdf']) }}" class="btn btn-danger ms-2"><i class="ti ti-file-type-pdf me-1"></i> Export PDF</a>
                <a id="exportPrint" href="{{ route('reports.students.directory.export', ['type' => 'print']) }}" class="btn btn-warning ms-2" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
            </form>
        </div>
    </div>

    <table id="directoryTable" class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Admission No</th>
                <th>Class & Section</th>
                <th>Gender</th>
                <th>Guardian</th>
                <th>Contact</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
@endsection

@push("scripts")
<script type="text/javascript">
    $(async function () {
        const DataTable = await window.lazyDT();
        var table = $("#directoryTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reports.students.directory') }}",
                data: function (d) {
                    d.academic_year_id = $('#academic_year_id').val();
                    d.class_section_id = $('#class_section_id').val();
                    d.status = $('#status').val();
                }
            },
            columns: [
                {data: "DT_RowIndex", name: "DT_RowIndex"},
                {data: "full_name", name: "full_name"},
                {data: "admission_no", name: "admission_no"},
                {data: "class_section", name: "class_section"},
                {data: "gender", name: "gender"},
                {data: "guardian", name: "guardian"},
                {data: "contact", name: "contact"},
            ]
        });

        function updateExportLinks() {
            var params = {
                academic_year_id: $('#academic_year_id').val(),
                class_section_id: $('#class_section_id').val(),
                status: $('#status').val()
            };
            var queryString = $.param(params);
            var baseExcel = "{{ route('reports.students.directory.export', ['type' => 'excel']) }}";
            var basePdf = "{{ route('reports.students.directory.export', ['type' => 'pdf']) }}";
            var basePrint = "{{ route('reports.students.directory.export', ['type' => 'print']) }}";

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
