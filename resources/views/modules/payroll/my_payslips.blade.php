@extends('layouts.admin')

@section('title', 'My Payslips')
@section('page-title', 'My Payslips')

@section('breadcrumbs')
    <li class="breadcrumb-item active">My Payslips</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mb-0"><i class="ti ti-cash text-primary me-2"></i>Payslip History</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped datatable" data-url="{{ route('admin.payroll.my-payslips.data') }}">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Gross Salary</th>
                                    <th>Total Deductions</th>
                                    <th>Net Salary</th>
                                    <th>Generated At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            if ($.fn.DataTable.isDataTable('.datatable')) {
                $('.datatable').DataTable().destroy();
            }

            $('.datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: $('.datatable').data('url'),
                columns: [
                    { data: 'period', name: 'period' },
                    { data: 'gross_salary', name: 'gross_salary' },
                    { data: 'total_deductions', name: 'total_deductions' },
                    { data: 'net_salary', name: 'net_salary' },
                    { data: 'generated_at', name: 'generated_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[4, 'desc']],
                pageLength: 25,
            });
        });
    </script>
@endpush
