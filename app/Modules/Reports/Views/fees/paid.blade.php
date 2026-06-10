@extends('layouts.admin')

@section("title", "Paid Fees Report")
@section("page-title", "Paid Fees Report")

@section("content")
    <div class="mb-3">
        <a href="{{ route('reports.fees.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i> Back to Fee Reports</a>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <form id="filterForm" class="form-inline">
                <div class="form-group mr-2 mb-2">
                    <label for="from_date" class="mr-2">From Date:</label>
                    <input type="date" name="from_date" id="from_date" class="form-control">
                </div>
                <div class="form-group mr-2 mb-2">
                    <label for="to_date" class="mr-2">To Date:</label>
                    <input type="date" name="to_date" id="to_date" class="form-control">
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
                <div class="form-group mr-2 mb-2">
                    <label for="payment_mode" class="mr-2">Payment Mode:</label>
                    <select name="payment_mode" id="payment_mode" class="form-control">
                        <option value="">All</option>
                        <option value="cash">Cash</option>
                        <option value="upi">UPI</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div class="form-group mb-2">
                    <button type="button" id="filterBtn" class="btn btn-primary mr-2">Filter</button>
                    <button type="button" id="resetBtn" class="btn btn-secondary mr-2">Reset</button>
                    <a id="exportExcel" href="{{ route('reports.fees.export.excel', ['type' => 'paid']) }}" class="btn btn-success mr-2">Export Excel</a>
                    <a id="exportPdf" href="{{ route('reports.fees.export.pdf', ['type' => 'paid']) }}" class="btn btn-danger mr-2">Export PDF</a>
                    <a id="exportPrint" href="{{ route('reports.fees.print', ['type' => 'paid']) }}" class="btn btn-warning" target="_blank">Print</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="paidTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Receipt No</th>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Admission No</th>
                            <th>Amount</th>
                            <th>Payment Mode</th>
                            <th>Collector</th>
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
        var table = $("#paidTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route("reports.fees.paid") }}",
                data: function (d) {
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.class_section_id = $('#class_section_id').val();
                    d.payment_mode = $('#payment_mode').val();
                }
            },
            columns: [
                {data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
                {data: "receipt_number", name: "receipt_number"},
                {data: "paid_on", name: "paid_on"},
                {data: "student", name: "student"},
                {data: "admission_no", name: "admission_no"},
                {data: "amount", name: "amount"},
                {
                    data: "payment_mode",
                    name: "payment_mode",
                    render: function(data, type, row) {
                        return '<span class="badge badge-info">' + data + '</span>';
                    }
                },
                {data: "collector", name: "collector"},
            ]
        });

        function updateExportLinks() {
            var params = {
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val(),
                class_section_id: $('#class_section_id').val(),
                payment_mode: $('#payment_mode').val()
            };
            var queryString = $.param(params);
            
            var baseExcel = "{{ route('reports.fees.export.excel', ['type' => 'paid']) }}";
            var basePdf = "{{ route('reports.fees.export.pdf', ['type' => 'paid']) }}";
            var basePrint = "{{ route('reports.fees.print', ['type' => 'paid']) }}";

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
