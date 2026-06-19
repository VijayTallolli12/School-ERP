@extends('layouts.admin')

@section('title', 'Payroll Reports')
@section('page-title', 'Payroll Reports')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.payroll.index') }}">Payroll</a></li>
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#departmentsPane" type="button"><i class="ti ti-building me-1"></i>Departments</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#designationsPane" type="button"><i class="ti ti-badge me-1"></i>Designations</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#salaryComponentsPane" type="button"><i class="ti ti-calculator me-1"></i>Salary Components</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#payGradesPane" type="button"><i class="ti ti-stairs me-1"></i>Pay Grades</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#salaryStructuresPane" type="button"><i class="ti ti-report-money me-1"></i>Salary Structures</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#employeeListPane" type="button"><i class="ti ti-users me-1"></i>Employee List</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="departmentsPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="deptFilterStatus"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="deptFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.payroll.reports.export.excel', 'departments') }}" id="deptExcel"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.payroll.reports.export.pdf', 'departments') }}" id="deptPdf"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.payroll.reports.print', 'departments') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="departmentsTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Sort Order</th><th>Designations</th><th>Status</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="designationsPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="desFilterDepartment"><option value="">All Departments</option>@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div>
                        <div class="col-auto"><select class="form-select form-select-sm" id="desFilterStatus"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="desFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.payroll.reports.export.excel', 'designations') }}" id="desExcel"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.payroll.reports.export.pdf', 'designations') }}" id="desPdf"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.payroll.reports.print', 'designations') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="designationsTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Department</th><th>Description</th><th>Status</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="salaryComponentsPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="scFilterType"><option value="">All Types</option><option value="earning">Earning</option><option value="deduction">Deduction</option></select></div>
                        <div class="col-auto"><select class="form-select form-select-sm" id="scFilterStatus"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="scFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.payroll.reports.export.excel', 'salary_components') }}" id="scExcel"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.payroll.reports.export.pdf', 'salary_components') }}" id="scPdf"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.payroll.reports.print', 'salary_components') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="salaryComponentsTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Display Name</th><th>Type</th><th>Calculation</th><th>Value</th><th>Sort Order</th><th>Status</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="payGradesPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="pgFilterStatus"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="pgFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.payroll.reports.export.excel', 'pay_grades') }}" id="pgExcel"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.payroll.reports.export.pdf', 'pay_grades') }}" id="pgPdf"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.payroll.reports.print', 'pay_grades') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="payGradesTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Min Salary</th><th>Max Salary</th><th>Status</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="salaryStructuresPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="ssFilterPayGrade"><option value="">All Pay Grades</option>@foreach($payGrades as $g)<option value="{{ $g->id }}">{{ $g->name }}</option>@endforeach</select></div>
                        <div class="col-auto"><select class="form-select form-select-sm" id="ssFilterType"><option value="">All Types</option><option value="teacher">Teacher</option><option value="staff">Staff</option></select></div>
                        <div class="col-auto"><select class="form-select form-select-sm" id="ssFilterStatus"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="ssFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.payroll.reports.export.excel', 'salary_structures') }}" id="ssExcel"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.payroll.reports.export.pdf', 'salary_structures') }}" id="ssPdf"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.payroll.reports.print', 'salary_structures') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="salaryStructuresTable">
                        <thead><tr><th>ID</th><th>Employee</th><th>Type</th><th>Pay Grade</th><th>Effective From</th><th>Effective To</th><th>Total CTC</th><th>Status</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="employeeListPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="elFilterPayGrade"><option value="">All Pay Grades</option>@foreach($payGrades as $g)<option value="{{ $g->id }}">{{ $g->name }}</option>@endforeach</select></div>
                        <div class="col-auto"><select class="form-select form-select-sm" id="elFilterType"><option value="">All Types</option><option value="teacher">Teacher</option><option value="staff">Staff</option></select></div>
                        <div class="col-auto"><select class="form-select form-select-sm" id="elFilterStatus"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="elFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.payroll.reports.export.excel', 'employee_list') }}" id="elExcel"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.payroll.reports.export.pdf', 'employee_list') }}" id="elPdf"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.payroll.reports.print', 'employee_list') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="employeeListTable">
                        <thead><tr><th>ID</th><th>Employee</th><th>Type</th><th>Pay Grade</th><th>Effective From</th><th>Effective To</th><th>Total CTC</th><th>Status</th></tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();

            const baseExcel = '{{ route('admin.payroll.reports.export.excel', 'REPLACE') }}';
            const basePdf = '{{ route('admin.payroll.reports.export.pdf', 'REPLACE') }}';
            const basePrint = '{{ route('admin.payroll.reports.print', 'REPLACE') }}';

            function updateExportLinks(prefix, reportKey, params) {
                const qs = $.param(params);
                $(`#${prefix}Excel`).attr('href', baseExcel.replace('REPLACE', reportKey) + '?' + qs);
                $(`#${prefix}Pdf`).attr('href', basePdf.replace('REPLACE', reportKey) + '?' + qs);
                $(`#${prefix}Print`).attr('href', basePrint.replace('REPLACE', reportKey) + '?' + qs);
            }

            const deptTable = $('#departmentsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.payroll.reports.departments.data') }}', data: d => { d.status = $('#deptFilterStatus').val(); }}, columns: [
                {data:'id'}, {data:'name'}, {data:'description'}, {data:'sort_order'}, {data:'designations_count', searchable:false}, {data:'status'}
            ]});
            $('#deptFilterBtn').on('click', () => { deptTable.ajax.reload(); updateExportLinks('dept', 'departments', {status: $('#deptFilterStatus').val()}); });

            const desTable = $('#designationsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.payroll.reports.designations.data') }}', data: d => { d.department_id = $('#desFilterDepartment').val(); d.status = $('#desFilterStatus').val(); }}, columns: [
                {data:'id'}, {data:'name'}, {data:'department_name', orderable:false}, {data:'description'}, {data:'status'}
            ]});
            $('#desFilterBtn').on('click', () => { desTable.ajax.reload(); updateExportLinks('des', 'designations', {department_id: $('#desFilterDepartment').val(), status: $('#desFilterStatus').val()}); });

            const scTable = $('#salaryComponentsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.payroll.reports.salary-components.data') }}', data: d => { d.component_type = $('#scFilterType').val(); d.status = $('#scFilterStatus').val(); }}, columns: [
                {data:'id'}, {data:'name'}, {data:'name_display'}, {data:'component_type'}, {data:'calculation_type'}, {data:'value'}, {data:'sort_order'}, {data:'status'}
            ]});
            $('#scFilterBtn').on('click', () => { scTable.ajax.reload(); updateExportLinks('sc', 'salary_components', {component_type: $('#scFilterType').val(), status: $('#scFilterStatus').val()}); });

            const pgTable = $('#payGradesTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.payroll.reports.pay-grades.data') }}', data: d => { d.status = $('#pgFilterStatus').val(); }}, columns: [
                {data:'id'}, {data:'name'}, {data:'description'}, {data:'min_salary'}, {data:'max_salary'}, {data:'status'}
            ]});
            $('#pgFilterBtn').on('click', () => { pgTable.ajax.reload(); updateExportLinks('pg', 'pay_grades', {status: $('#pgFilterStatus').val()}); });

            const ssTable = $('#salaryStructuresTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.payroll.reports.salary-structures.data') }}', data: d => { d.pay_grade_id = $('#ssFilterPayGrade').val(); d.employee_type = $('#ssFilterType').val(); d.status = $('#ssFilterStatus').val(); }}, columns: [
                {data:'id'}, {data:'employee_name', orderable:false}, {data:'employee_type', orderable:false}, {data:'pay_grade_name', orderable:false}, {data:'effective_from'}, {data:'effective_to'}, {data:'total_ctc'}, {data:'status'}
            ]});
            $('#ssFilterBtn').on('click', () => { ssTable.ajax.reload(); updateExportLinks('ss', 'salary_structures', {pay_grade_id: $('#ssFilterPayGrade').val(), employee_type: $('#ssFilterType').val(), status: $('#ssFilterStatus').val()}); });

            const elTable = $('#employeeListTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.payroll.reports.employee-list.data') }}', data: d => { d.pay_grade_id = $('#elFilterPayGrade').val(); d.employee_type = $('#elFilterType').val(); d.status = $('#elFilterStatus').val(); }}, columns: [
                {data:'id'}, {data:'employee_name', orderable:false}, {data:'employee_type', orderable:false}, {data:'pay_grade_name', orderable:false}, {data:'effective_from'}, {data:'effective_to'}, {data:'total_ctc'}, {data:'status'}
            ]});
            $('#elFilterBtn').on('click', () => { elTable.ajax.reload(); updateExportLinks('el', 'employee_list', {pay_grade_id: $('#elFilterPayGrade').val(), employee_type: $('#elFilterType').val(), status: $('#elFilterStatus').val()}); });

            initTabPersistence('#reportTabs');
        })(); });
    </script>
@endpush
