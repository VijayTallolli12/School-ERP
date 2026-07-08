@extends("Reports::reports_layout")

@section("title", "Gender-wise Student Report")
@section("report_title", "Gender-wise Student Report")

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
                <button type="button" id="filterBtn" class="btn btn-primary"><i class="ti ti-filter me-1"></i> Filter</button>
                <button type="button" id="resetBtn" class="btn btn-secondary ms-2"><i class="ti ti-refresh me-1"></i> Reset</button>
                <a id="exportExcel" href="{{ route('reports.students.gender_wise.export', ['type' => 'excel']) }}" class="btn btn-success ms-2"><i class="ti ti-file-spreadsheet me-1"></i> Export Excel</a>
                <a id="exportPdf" href="{{ route('reports.students.gender_wise.export', ['type' => 'pdf']) }}" class="btn btn-danger ms-2"><i class="ti ti-file-type-pdf me-1"></i> Export PDF</a>
                <a id="exportPrint" href="{{ route('reports.students.gender_wise.export', ['type' => 'print']) }}" class="btn btn-warning ms-2" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
            </form>
        </div>
    </div>

    <table id="genderTable" class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Class & Section</th>
                <th>Male</th>
                <th>Female</th>
                <th>Other</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
@endsection

@push("scripts")
<script type="text/javascript">
    $(async function () {
        const DataTable = await window.lazyDT();
        var table = $("#genderTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reports.students.gender_wise') }}",
                data: function (d) {
                    d.academic_year_id = $('#academic_year_id').val();
                    d.class_section_id = $('#class_section_id').val();
                }
            },
            columns: [
                {data: "DT_RowIndex", name: "DT_RowIndex"},
                {data: "class_section", name: "class_section"},
                {data: "male", name: "male"},
                {data: "female", name: "female"},
                {data: "other", name: "other"},
                {data: "total", name: "total"},
            ]
        });

        function updateExportLinks() {
            var params = {
                academic_year_id: $('#academic_year_id').val(),
                class_section_id: $('#class_section_id').val()
            };
            var queryString = $.param(params);
            $('#exportExcel').attr('href', "{{ route('reports.students.gender_wise.export', ['type' => 'excel']) }}" + (queryString ? '?' + queryString : ''));
            $('#exportPdf').attr('href', "{{ route('reports.students.gender_wise.export', ['type' => 'pdf']) }}" + (queryString ? '?' + queryString : ''));
            $('#exportPrint').attr('href', "{{ route('reports.students.gender_wise.export', ['type' => 'print']) }}" + (queryString ? '?' + queryString : ''));
        }

        $('#filterBtn').on('click', function() {
            table.ajax.reload();
            updateExportLinks();
        });

        $('#resetBtn').on('click', function() {
            $('#academic_year_id').val('');
            $('#class_section_id').val('');
            table.ajax.reload();
            updateExportLinks();
        });

        updateExportLinks();
    });
</script>
@endpush
