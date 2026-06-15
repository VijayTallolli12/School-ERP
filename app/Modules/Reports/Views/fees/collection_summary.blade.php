@extends('layouts.admin')

@section("title", "Collection Summary Report")
@section("page-title", "Collection Summary Report")

@section("content")
    <div class="mb-3">
        <a href="{{ route('reports.fees.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i> Back to Fee Reports</a>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="me-2 mb-2">
                    <label for="academic_year_id" class="form-label me-2">Academic Year:</label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <button type="button" id="filterBtn" class="btn btn-primary me-2"><i class="ti ti-filter me-1"></i> Filter</button>
                    <button type="button" id="resetBtn" class="btn btn-secondary me-2"><i class="ti ti-refresh me-1"></i> Reset</button>
                    <a id="exportExcel" href="{{ route('reports.fees.export.excel', ['type' => 'collection_summary']) }}" class="btn btn-success me-2"><i class="ti ti-file-spreadsheet me-1"></i> Export Excel</a>
                    <a id="exportPdf" href="{{ route('reports.fees.export.pdf', ['type' => 'collection_summary']) }}" class="btn btn-danger me-2"><i class="ti ti-file-type-pdf me-1"></i> Export PDF</a>
                    <a id="exportPrint" href="{{ route('reports.fees.print', ['type' => 'collection_summary']) }}" class="btn btn-warning" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
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
                            <th>Class & Section</th>
                            <th>Total Due</th>
                            <th>Total Paid</th>
                            <th>Balance</th>
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
                url: "{{ route("reports.fees.collection_summary") }}",
                data: function (d) {
                    d.academic_year_id = $('#academic_year_id').val();
                }
            },
            columns: [
                {data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
                {data: "class_section", name: "class_section"},
                {data: "total_due", name: "total_due"},
                {data: "total_paid", name: "total_paid"},
                {
                    data: "balance", 
                    name: "balance",
                    render: function(data, type, row) {
                        if (data > 0) {
                            return '<span class="text-danger font-weight-bold">' + data + '</span>';
                        }
                        return '<span class="text-success font-weight-bold">' + data + '</span>';
                    }
                },
            ]
        });

        function updateExportLinks() {
            var params = {
                academic_year_id: $('#academic_year_id').val()
            };
            var queryString = $.param(params);
            
            var baseExcel = "{{ route('reports.fees.export.excel', ['type' => 'collection_summary']) }}";
            var basePdf = "{{ route('reports.fees.export.pdf', ['type' => 'collection_summary']) }}";
            var basePrint = "{{ route('reports.fees.print', ['type' => 'collection_summary']) }}";

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
