@extends('layouts.admin')

@section("title", "Subject Performance Report")
@section("page-title", "Subject Performance Report")

@section("content")
    <div class="mb-3">
        <a href="{{ route('reports.exams.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i> Back to Exam Reports</a>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <form id="filterForm" class="form-inline">
                <div class="form-group mr-2 mb-2">
                    <label for="academic_year_id" class="mr-2">Academic Year:</label>
                    <select name="academic_year_id" id="academic_year_id" class="form-control">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-2 mb-2">
                    <label for="exam_id" class="mr-2">Exam:</label>
                    <select name="exam_id" id="exam_id" class="form-control">
                        <option value="">All</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}">{{ $exam->exam_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-2 mb-2">
                    <label for="class_section_id" class="mr-2">Class & Section:</label>
                    <select name="class_section_id" id="class_section_id" class="form-control">
                        <option value="">All</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-2">
                    <button type="button" id="filterBtn" class="btn btn-primary mr-2">Filter</button>
                    <button type="button" id="resetBtn" class="btn btn-secondary mr-2">Reset</button>
                    <a id="exportExcel" href="{{ route('reports.exams.export.excel', ['type' => 'subject_performance']) }}" class="btn btn-success mr-2">Export Excel</a>
                    <a id="exportPdf" href="{{ route('reports.exams.export.pdf', ['type' => 'subject_performance']) }}" class="btn btn-danger mr-2">Export PDF</a>
                    <a id="exportPrint" href="{{ route('reports.exams.print', ['type' => 'subject_performance']) }}" class="btn btn-warning" target="_blank">Print</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="performanceTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Class & Section</th>
                            <th>Exam Name</th>
                            <th>Total Students</th>
                            <th>Highest Marks</th>
                            <th>Lowest Marks</th>
                            <th>Average Marks</th>
                            <th>Pass %</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables will load data here via AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
<script type="text/javascript">
    $(function () {
        var table = $("#performanceTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route("reports.exams.subject_performance") }}",
                data: function (d) {
                    d.academic_year_id = $('#academic_year_id').val();
                    d.exam_id = $('#exam_id').val();
                    d.class_section_id = $('#class_section_id').val();
                }
            },
            columns: [
                {data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
                {data: "subject", name: "subject"},
                {data: "class_section", name: "class_section"},
                {data: "exam_name", name: "exam_name"},
                {data: "total_students", name: "total_students"},
                {
                    data: "highest_marks", 
                    name: "highest_marks",
                    render: function(data, type, row) {
                        return '<span class="text-success font-weight-bold">' + data + '</span>';
                    }
                },
                {
                    data: "lowest_marks", 
                    name: "lowest_marks",
                    render: function(data, type, row) {
                        return '<span class="text-danger font-weight-bold">' + data + '</span>';
                    }
                },
                {data: "average_marks", name: "average_marks"},
                {data: "pass_percentage", name: "pass_percentage"},
            ]
        });

        function updateExportLinks() {
            var params = {
                academic_year_id: $('#academic_year_id').val(),
                exam_id: $('#exam_id').val(),
                class_section_id: $('#class_section_id').val()
            };
            var queryString = $.param(params);
            
            var baseExcel = "{{ route('reports.exams.export.excel', ['type' => 'subject_performance']) }}";
            var basePdf = "{{ route('reports.exams.export.pdf', ['type' => 'subject_performance']) }}";
            var basePrint = "{{ route('reports.exams.print', ['type' => 'subject_performance']) }}";

            $('#exportExcel').attr('href', baseExcel + (queryString ? '?' + queryString : ''));
            $('#exportPdf').attr('href', basePdf + (queryString ? '?' + queryString : ''));
            $('#exportPrint').attr('href', basePrint + (queryString ? '?' + queryString : ''));
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