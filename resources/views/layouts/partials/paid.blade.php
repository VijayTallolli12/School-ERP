@extends('modules.reports.reports_layout')

@section('title', 'Paid Fees Report')
@section('report_title', 'Paid Fees Report')

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Class Section</label>
                    <select name="class_section_id" id="class_section_id" class="form-select">
                        <option value="">All</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Payment Mode</label>
                    <select name="payment_mode" id="payment_mode" class="form-select">
                        <option value="">All</option>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="online">Online</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <button type="button" id="filterBtn" class="btn btn-primary btn-sm mt-1"><i class="ti ti-filter"></i></button>
                    <button type="button" id="resetBtn" class="btn btn-secondary btn-sm mt-1"><i class="ti ti-refresh"></i></button>
                    <a id="exportExcel" href="#" class="btn btn-success btn-sm mt-1" title="Excel"><i class="ti ti-file-type-xls"></i></a>
                    <a id="exportPdf" href="#" class="btn btn-danger btn-sm mt-1" title="PDF"><i class="ti ti-file-type-pdf"></i></a>
                    <a id="exportPrint" href="#" class="btn btn-info btn-sm mt-1" target="_blank" title="Print"><i class="ti ti-printer"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table id="dataTable" class="table table-striped table-bordered w-100">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Receipt No</th>
                    <th>Student</th>
                    <th>Class Section</th>
                    <th>Mode</th>
                    <th>Amount</th>
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
                url: "{{ route('reports.fees.paid') }}",
                data: function (d) {
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.class_section_id = $('#class_section_id').val();
                    d.payment_mode = $('#payment_mode').val();
                }
            },
            columns: [
                {data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
                {data: "paid_on", name: "paid_on", defaultContent: "-"},
                {data: "receipt_number", name: "receipt_number", defaultContent: "-"},
                {data: "student", name: "student", defaultContent: "-"},
                {data: "class_section", name: "class_section", defaultContent: "-"},
                {data: "payment_mode", name: "payment_mode", defaultContent: "-"},
                {data: "amount", name: "amount", defaultContent: "0.00"}
            ]
        });

        function updateExportLinks() {
            var qs = $('#filterForm').serialize();
            $('#exportExcel').attr('href', "{{ route('reports.fees.export.excel', 'paid') }}?" + qs);
            $('#exportPdf').attr('href', "{{ route('reports.fees.export.pdf', 'paid') }}?" + qs);
            $('#exportPrint').attr('href', "{{ route('reports.fees.print', 'paid') }}?" + qs);
        }
        $('#filterBtn').on('click', function() { table.ajax.reload(); updateExportLinks(); });
        $('#resetBtn').on('click', function() { $('#filterForm')[0].reset(); table.ajax.reload(); updateExportLinks(); });
        updateExportLinks();
    });
</script>
@endpush