@extends('layouts.admin')

@section("title", "Pending Fees Report")
@section("page-title", "Pending Fees Report")

@section("content")
    <div class="mb-3">
        <a href="{{ route('reports.fees.index') }}" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Fee Reports</a>
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
                <div class="form-group mb-2">
                    <button type="button" id="filterBtn" class="btn btn-primary mr-2">Filter</button>
                    <button type="button" id="resetBtn" class="btn btn-secondary mr-2">Reset</button>
                    <a id="exportExcel" href="{{ route('reports.fees.export.excel', ['type' => 'pending']) }}" class="btn btn-success mr-2">Export Excel</a>
                    <a id="exportPdf" href="{{ route('reports.fees.export.pdf', ['type' => 'pending']) }}" class="btn btn-danger mr-2">Export PDF</a>
                    <a id="exportPrint" href="{{ route('reports.fees.print', ['type' => 'pending']) }}" class="btn btn-warning" target="_blank">Print</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="pendingTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Admission No</th>
                            <th>Academic Year</th>
                            <th>Category</th>
                            <th>Amount Due</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Due Date</th>
                            <th>Overdue</th>
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
        var table = $("#pendingTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route("reports.fees.pending") }}",
                data: function (d) {
                    d.academic_year_id = $('#academic_year_id').val();
                }
            },
            columns: [
                {data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
                {data: "student", name: "student"},
                {data: "admission_no", name: "admission_no"},
                {data: "academic_year", name: "academic_year"},
                {data: "category", name: "category"},
                {data: "amount", name: "amount"},
                {data: "paid", name: "paid"},
                {
                    data: "balance", 
                    name: "balance",
                    render: function(data, type, row) {
                        return '<span class="text-danger font-weight-bold">' + data + '</span>';
                    }
                },
                {data: "due_date", name: "due_date"},
                {
                    data: "overdue", 
                    name: "overdue",
                    render: function(data, type, row) {
                        return data === 'Yes' 
                            ? '<span class="badge badge-danger">Yes</span>' 
                            : '<span class="badge badge-success">No</span>';
                    }
                },
            ]
        });

        function updateExportLinks() {
            var params = {
                academic_year_id: $('#academic_year_id').val()
            };
            var queryString = $.param(params);
            
            var baseExcel = "{{ route('reports.fees.export.excel', ['type' => 'pending']) }}";
            var basePdf = "{{ route('reports.fees.export.pdf', ['type' => 'pending']) }}";
            var basePrint = "{{ route('reports.fees.print', ['type' => 'pending']) }}";

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
