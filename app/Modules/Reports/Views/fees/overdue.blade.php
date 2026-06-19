@extends('layouts.admin')

@section("title", "Overdue Fees Report")
@section("page-title", "Overdue Fees Report")

@section("content")
    <div class="mb-3">
        <a href="{{ route('reports.fees.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i> Back to Fee Reports</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="academic_year_id" class="form-label">Academic Year</label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="button" id="filterBtn" class="btn btn-primary"><i class="ti ti-filter me-1"></i> Filter</button>
                    <button type="button" id="resetBtn" class="btn btn-outline-secondary"><i class="ti ti-refresh"></i> Reset</button>
                </div>
            </form>
            <div class="mt-3">
                <button type="button" id="exportExcel" class="btn btn-success me-2"><i class="ti ti-file-type-xls me-1"></i> Export Excel</button>
                <button type="button" id="exportPdf" class="btn btn-danger me-2"><i class="ti ti-file-type-pdf me-1"></i> Export PDF</button>
                <button type="button" id="exportPrint" class="btn btn-warning"><i class="ti ti-printer me-1"></i> Print</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ti ti-alert-triangle text-danger me-2"></i>Overdue Fee Records</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="overdueTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Admission No</th>
                            <th>Academic Year</th>
                            <th>Fee Category</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Due Date</th>
                            <th>Overdue</th>
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
    $(async function() {
        const DataTable = await window.lazyDT();
        var table = $("#overdueTable").DataTable({
            processing: true,
            serverSide: false,
            data: [],
            columns: [
                {data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
                {data: "student", name: "student"},
                {data: "admission_no", name: "admission_no"},
                {data: "academic_year", name: "academic_year"},
                {data: "category", name: "category"},
                {data: "amount", name: "amount", className: "text-end", render: function(d) { return '₹ ' + Number(d).toLocaleString('en-IN', {minimumFractionDigits: 2}); }},
                {data: "paid", name: "paid", className: "text-end", render: function(d) { return '₹ ' + Number(d).toLocaleString('en-IN', {minimumFractionDigits: 2}); }},
                {data: "balance", name: "balance", className: "text-end", render: function(d) { return '<span class="text-danger fw-bold">₹ ' + Number(d).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</span>'; }},
                {data: "due_date", name: "due_date"},
                {data: "overdue", name: "overdue", className: "text-center", render: function(d) {
                    return d === 'Yes' ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-success">No</span>';
                }},
            ],
            order: [[7, 'desc']],
            pageLength: 25,
        });

        function loadData() {
            var params = { academic_year_id: $('#academic_year_id').val() };
            $.get("{{ route('reports.fees.overdue') }}", params, function(data) {
                table.clear().rows.add(data).draw();
                updateExportLinks();
            });
        }

        function updateExportLinks() {
            var qs = $.param({ academic_year_id: $('#academic_year_id').val() });
            var baseExcel = "{{ route('reports.fees.export.excel', ['type' => 'overdue']) }}";
            var basePdf = "{{ route('reports.fees.export.pdf', ['type' => 'overdue']) }}";
            var basePrint = "{{ route('reports.fees.print', ['type' => 'overdue']) }}";
            $('#exportExcel').off('click').on('click', function() { window.location.href = baseExcel + (qs ? '?' + qs : ''); });
            $('#exportPdf').off('click').on('click', function() { window.open(basePdf + (qs ? '?' + qs : ''), '_blank'); });
            $('#exportPrint').off('click').on('click', function() { window.open(basePrint + (qs ? '?' + qs : ''), '_blank'); });
        }

        $('#filterBtn').on('click', loadData);
        $('#resetBtn').on('click', function() {
            $('#filterForm')[0].reset();
            loadData();
        });

        loadData();
    });
</script>
@endpush
