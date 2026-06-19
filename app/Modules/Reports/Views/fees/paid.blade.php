@extends('layouts.admin')

@section("title", "Paid Fees Report")
@section("page-title", "Paid Fees Report")

@section("content")
    <div class="mb-3">
        <a href="{{ route('reports.fees.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i> Back to Fee Reports</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="class_section_id" class="form-label">Class & Section</label>
                    <select name="class_section_id" id="class_section_id" class="form-select">
                        <option value="">All</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="payment_mode" class="form-label">Payment Mode</label>
                    <select name="payment_mode" id="payment_mode" class="form-select">
                        <option value="">All</option>
                        <option value="cash">Cash</option>
                        <option value="online">Online</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
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
            <h5 class="card-title mb-0"><i class="ti ti-receipt text-primary me-2"></i>Payment Records</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="paidTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Receipt No</th>
                            <th>Student</th>
                            <th>Admission No</th>
                            <th class="text-end">Amount</th>
                            <th>Payment Mode</th>
                            <th>Paid On</th>
                            <th>Collector</th>
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
        var table = $("#paidTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reports.fees.paid') }}",
                data: function(d) {
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.class_section_id = $('#class_section_id').val();
                    d.payment_mode = $('#payment_mode').val();
                }
            },
            columns: [
                {data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
                {data: "receipt_number", name: "receipt_number"},
                {data: "student", name: "student"},
                {data: "admission_no", name: "admission_no"},
                {data: "amount", name: "amount", className: "text-end", render: function(d) { return '₹ ' + Number(d).toLocaleString('en-IN', {minimumFractionDigits: 2}); }},
                {data: "payment_mode", name: "payment_mode"},
                {data: "paid_on", name: "paid_on"},
                {data: "collector", name: "collector"},
            ],
            order: [[1, 'desc']],
            pageLength: 25,
        });

        function updateExportLinks() {
            var qs = $.param({
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val(),
                class_section_id: $('#class_section_id').val(),
                payment_mode: $('#payment_mode').val(),
            });
            var baseExcel = "{{ route('reports.fees.export.excel', ['type' => 'paid']) }}";
            var basePdf = "{{ route('reports.fees.export.pdf', ['type' => 'paid']) }}";
            var basePrint = "{{ route('reports.fees.print', ['type' => 'paid']) }}";
            $('#exportExcel').off('click').on('click', function() { window.location.href = baseExcel + (qs ? '?' + qs : ''); });
            $('#exportPdf').off('click').on('click', function() { window.open(basePdf + (qs ? '?' + qs : ''), '_blank'); });
            $('#exportPrint').off('click').on('click', function() { window.open(basePrint + (qs ? '?' + qs : ''), '_blank'); });
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
