@extends('modules.reports.reports_layout')

@section('title', 'Pending Fees Report')
@section('report_title', 'Pending Fees Report')

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select">
                        <option value="">All Academic Years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8 text-end">
                    <button type="button" id="filterBtn" class="btn btn-primary"><i class="ti ti-filter me-1"></i> Filter</button>
                    <button type="button" id="resetBtn" class="btn btn-secondary ms-2"><i class="ti ti-refresh me-1"></i> Reset</button>
                    <a id="exportExcel" href="#" class="btn btn-success ms-2"><i class="ti ti-file-type-xls me-1"></i> Excel</a>
                    <a id="exportPdf" href="#" class="btn btn-danger ms-2"><i class="ti ti-file-type-pdf me-1"></i> PDF</a>
                    <a id="exportPrint" href="#" class="btn btn-info ms-2" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table id="dataTable" class="table table-striped table-bordered w-100">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Admission No</th>
                    <th>Class Section</th>
                    <th>Fee Category</th>
                    <th>Due Date</th>
                    <th>Balance Amount</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script type="text/javascript">
    $(function () {
        var table = $("#dataTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reports.fees.pending') }}",
                data: function (d) {
                    d.academic_year_id = $('#academic_year_id').val();
                }
            },
            columns: [
                {data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
                {data: "student_name", name: "student_name", defaultContent: "-"},
                {data: "admission_no", name: "admission_no", defaultContent: "-"},
                {data: "class_section", name: "class_section", defaultContent: "-"},
                {data: "category", name: "category", defaultContent: "-"},
                {data: "due_date", name: "due_date", defaultContent: "-"},
                {data: "balance", name: "balance", defaultContent: "0.00"}
            ]
        });

        function updateExportLinks() {
            var qs = $.param({ academic_year_id: $('#academic_year_id').val() });
            $('#exportExcel').attr('href', "{{ route('reports.fees.export.excel', 'pending') }}?" + qs);
            $('#exportPdf').attr('href', "{{ route('reports.fees.export.pdf', 'pending') }}?" + qs);
            $('#exportPrint').attr('href', "{{ route('reports.fees.print', 'pending') }}?" + qs);
        }

        $('#filterBtn').on('click', function() { table.ajax.reload(); updateExportLinks(); });
        $('#resetBtn').on('click', function() { $('#filterForm')[0].reset(); table.ajax.reload(); updateExportLinks(); });
        
        updateExportLinks();
    });
</script>
@endpush