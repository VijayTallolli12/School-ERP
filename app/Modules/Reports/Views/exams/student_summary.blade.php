@extends('layouts.admin')

@section("title", "Student Result Summary")
@section("page-title", "Student Result Summary")

@section("content")
    <div class="mb-3">
        <a href="{{ route('reports.exams.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i> Back to Exam Reports</a>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-auto mb-2">
                    <label for="student_id" class="form-label me-2">Student:</label>
                    <select name="student_id" id="student_id" class="form-select">
                        <option value="">Select Student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->admission_no }} - {{ $student->first_name }} {{ $student->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto mb-2">
                    <label for="academic_year_id" class="form-label me-2">Academic Year:</label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto mb-2">
                    <button type="button" id="filterBtn" class="btn btn-primary me-2"><i class="ti ti-filter me-1"></i>Filter</button>
                    <button type="button" id="resetBtn" class="btn btn-secondary me-2"><i class="ti ti-refresh me-1"></i>Reset</button>
                    <a id="exportExcel" href="{{ route('reports.exams.export.excel', ['type' => 'student_summary']) }}" class="btn btn-success me-2"><i class="ti ti-file-spreadsheet me-1"></i>Export Excel</a>
                    <a id="exportPdf" href="{{ route('reports.exams.export.pdf', ['type' => 'student_summary']) }}" class="btn btn-danger me-2"><i class="ti ti-file-type-pdf me-1"></i>Export PDF</a>
                    <a id="exportPrint" href="{{ route('reports.exams.print', ['type' => 'student_summary']) }}" class="btn btn-warning" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="summaryTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Exam Name</th>
                            <th>Academic Year</th>
                            <th>Total Obtained</th>
                            <th>Total Maximum</th>
                            <th>Percentage</th>
                            <th>Overall Grade</th>
                            <th>Status</th>
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
    $(async function () {
        const DataTable = await window.lazyDT();
        var table = $("#summaryTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route("reports.exams.student_summary") }}",
                data: function (d) {
                    d.student_id = $('#student_id').val();
                    d.academic_year_id = $('#academic_year_id').val();
                }
            },
            columns: [
                {data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
                {data: "exam_name", name: "exam_name"},
                {data: "academic_year", name: "academic_year"},
                {data: "total_obtained", name: "total_obtained"},
                {data: "total_maximum", name: "total_maximum"},
                {data: "percentage", name: "percentage"},
                {
                    data: "overall_grade", 
                    name: "overall_grade",
                    render: function(data, type, row) {
                        return '<span class="badge bg-primary">' + data + '</span>';
                    }
                },
                {
                    data: "status", 
                    name: "status",
                    render: function(data, type, row) {
                        return data === 'Pass' 
                            ? '<span class="badge bg-success">Pass</span>' 
                            : '<span class="badge bg-danger">Fail</span>';
                    }
                },
            ]
        });

        function updateExportLinks() {
            var params = {
                student_id: $('#student_id').val(),
                academic_year_id: $('#academic_year_id').val()
            };
            var queryString = $.param(params);
            
            var baseExcel = "{{ route('reports.exams.export.excel', ['type' => 'student_summary']) }}";
            var basePdf = "{{ route('reports.exams.export.pdf', ['type' => 'student_summary']) }}";
            var basePrint = "{{ route('reports.exams.print', ['type' => 'student_summary']) }}";

            $('#exportExcel').attr('href', baseExcel + (queryString ? '?' + queryString : ''));
            $('#exportPdf').attr('href', basePdf + (queryString ? '?' + queryString : ''));
            $('#exportPrint').attr('href', basePrint + (queryString ? '?' + queryString : ''));
        }

        $('#filterBtn').on('click', function() {
            if (!$('#student_id').val()) {
                alert('Please select a student to view the report.');
                return;
            }
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
