@extends("modules.reports.reports_layout")

@section("title", "Class-Wise Student Report")
@section("report_title", "Class-Wise Student Report")

@section("content")
    <div class="row mb-3">
        <div class="col-md-12">
            <form id="filterForm" class="form-inline">
                <div class="form-group mr-3">
                    <label for="academic_year_id" class="mr-2">Academic Year:</label>
                    <select name="academic_year_id" id="academic_year_id" class="form-control">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-3">
                    <label for="class_section_id" class="mr-2">Class & Section:</label>
                    <select name="class_section_id" id="class_section_id" class="form-control">
                        <option value="">All</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" id="filterBtn" class="btn btn-primary py-2"><i class="ti ti-filter me-1"></i> Filter</button>
                <button type="button" id="resetBtn" class="btn btn-outline-secondary py-2"><i class="ti ti-refresh me-1"></i> Reset</button>
                <a id="exportExcel" href="{{ route('reports.students.class_wise.export', ['type' => 'excel']) }}" class="btn btn-success py-2"><i class="ti ti-file-type-xls me-1"></i> Export Excel</a>
                <a id="exportPdf" href="{{ route('reports.students.class_wise.export', ['type' => 'pdf']) }}" class="btn btn-danger py-2"><i class="ti ti-file-type-pdf me-1"></i> Export PDF</a>
                <a id="exportPrint" href="{{ route('reports.students.class_wise.export', ['type' => 'print']) }}" class="btn btn-warning py-2" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
            </form>
        </div>
    </div>

    <table id="classWiseTable" class="table table-bordered">
        <thead>
            <tr>
                <th>Class</th>
                <th>Total Students</th>
                <th>Male</th>
                <th>Female</th>
                <th>Active</th>
                <th>Inactive</th>
            </tr>
        </thead>
        <tbody>
            {{-- DataTables will load data here via AJAX --}}
        </tbody>
    </table>
@endsection

@push("scripts")
<script type="text/javascript">
    $(function () {
        var table = $("#classWiseTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route("reports.students.class_wise") }}",
                data: function (d) {
                    d.academic_year_id = $('#academic_year_id').val();
                    d.class_section_id = $('#class_section_id').val();
                }
            },
            columns: [
                {data: "class_name", name: "class_name"},
                {data: "total_students", name: "total_students"},
                {data: "male_count", name: "male_count"},
                {data: "female_count", name: "female_count"},
                {data: "active_count", name: "active_count"},
                {data: "inactive_count", name: "inactive_count"},
            ]
        });

        function updateExportLinks() {
            var params = {
                academic_year_id: $('#academic_year_id').val(),
                class_section_id: $('#class_section_id').val()
            };
            var queryString = $.param(params);
            var baseExcel = "{{ route('reports.students.class_wise.export', ['type' => 'excel']) }}";
            var basePdf = "{{ route('reports.students.class_wise.export', ['type' => 'pdf']) }}";
            var basePrint = "{{ route('reports.students.class_wise.export', ['type' => 'print']) }}";

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
            table.ajax.reload();
            updateExportLinks();
        });

        updateExportLinks();
    });
</script>
@endpush
