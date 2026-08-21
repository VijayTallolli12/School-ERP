@extends('layouts.admin')

@section('title', 'Payroll Management')
@section('page-title', 'Payroll Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Payroll</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="payrollTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#departmentsPane" type="button">Departments</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#designationsPane" type="button">Designations</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#salaryComponentsPane" type="button">Salary Components</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#payGradesPane" type="button">Pay Grades</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#salaryStructuresPane" type="button">Salary Structures</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#payrollRunsPane" type="button">Payroll Runs</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#payslipsPane" type="button">Payslips</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="departmentsPane">
                    <div class="d-flex mb-3">
                        @can('payroll.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#departmentOffcanvas">Add Department</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="departmentsTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Sort Order</th><th>Designations</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="designationsPane">
                    <div class="d-flex mb-3">
                        @can('payroll.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#designationOffcanvas">Add Designation</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="designationsTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Department</th><th>Description</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="salaryComponentsPane">
                    <div class="d-flex mb-3">
                        @can('payroll.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#salaryComponentOffcanvas">Add Salary Component</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="salaryComponentsTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Display Name</th><th>Type</th><th>Calculation</th><th>Value</th><th>Sort Order</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="payGradesPane">
                    <div class="d-flex mb-3">
                        @can('payroll.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#payGradeOffcanvas">Add Pay Grade</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="payGradesTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Min Salary</th><th>Max Salary</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="salaryStructuresPane">
                    <div class="d-flex mb-3">
                        @can('payroll.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#salaryStructureOffcanvas">Add Salary Structure</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="salaryStructuresTable">
                        <thead><tr><th>ID</th><th>Employee</th><th>Type</th><th>Pay Grade</th><th>Effective From</th><th>Effective To</th><th>Total CTC</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="payrollRunsPane">
                    <div class="d-flex mb-3">
                        @can('payroll.process')
                            <button class="btn btn-primary btn-sm ms-auto" id="generatePayrollBtn">Generate Payroll</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="payrollRunsTable">
                        <thead><tr><th>ID</th><th>Period</th><th>Status</th><th>Generated At</th><th>Employees</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="payslipsPane">
                    <div class="row g-2 mb-3 align-items-center">
                        <div class="col-auto">
                            <select class="form-select form-select-sm" id="psFilterRun" style="min-width:250px;">
                                <option value="">Select a locked payroll run</option>
                                @foreach($payrollRuns ?? [] as $r)
                                    <option value="{{ $r->id }}">{{ $r->month_name }} {{ $r->year }} ({{ $r->status }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            @can('payroll.payslip.generate')
                                <button class="btn btn-sm btn-primary" id="bulkGenerateBtn" disabled>Generate All Payslips</button>
                            @endcan
                        </div>
                        <div class="col-auto ms-auto">
                            <small class="text-muted">Select a locked run to view or generate payslips</small>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="payslipsTable">
                        <thead><tr><th>Payslip #</th><th>Employee</th><th>Period</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Generated At</th><th width="150">Actions</th></tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

<x-erp.side-panel 
    id="generatePayrollOffcanvas" 
    formId="generatePayrollForm" 
    title="Generate Payroll"
    action="{{ route('admin.payroll.runs.generate') }}" 
    method="POST" 
    width="600px" 
    saveButtonText="Generate"
>
    <div class="row g-4">
        <div class="col-md-6"><label class="form-label required fw-medium text-dark">Month</label><select class="form-select" name="month" required>@foreach(range(1,12) as $m)<option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label required fw-medium text-dark">Year</label><input class="form-control" type="number" name="year" min="2020" max="2099" value="{{ date('Y') }}" required></div>
        <div class="col-12"><label class="form-label fw-medium text-dark">Notes</label><textarea class="form-control" name="notes" rows="2" maxlength="500"></textarea></div>
    </div>
</x-erp.side-panel>

<x-erp.side-panel 
    id="runDetailOffcanvas" 
    title="Payroll Run Details"
    width="1000px" 
>
    <div class="row g-4 mb-3" id="runSummary"></div>
    <table class="table table-striped table-bordered w-100" id="runItemsTable">
        <thead><tr><th>Employee</th><th>Type</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Status</th></tr></thead>
    </table>
</x-erp.side-panel>

@push('modals')
    <x-erp.side-panel 
        id="departmentOffcanvas" 
        formId="departmentForm" 
        title="Department"
        action="{{ route('admin.payroll.departments.store') }}" 
        method="POST" 
        width="500px" 
        saveButtonText="Save"
    >
        <div class="row g-4">
            <div class="col-12"><label class="form-label required fw-medium text-dark">Name</label><input class="form-control" name="name" required></div>
            <div class="col-12"><label class="form-label fw-medium text-dark">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Sort Order</label><input class="form-control" type="number" name="sort_order" min="0" value="0"></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel 
        id="designationOffcanvas" 
        formId="designationForm" 
        title="Designation"
        action="{{ route('admin.payroll.designations.store') }}" 
        method="POST" 
        width="600px" 
        saveButtonText="Save"
    >
        <div class="row g-4">
            <div class="col-12"><label class="form-label required fw-medium text-dark">Name</label><input class="form-control" name="name" required></div>
            <div class="col-12"><label class="form-label fw-medium text-dark">Department</label><select class="form-select" name="department_id"><option value="">Select</option>@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div>
            <div class="col-12"><label class="form-label fw-medium text-dark">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel 
        id="salaryComponentOffcanvas" 
        formId="salaryComponentForm" 
        title="Salary Component"
        action="{{ route('admin.payroll.salary-components.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Save"
    >
        <div class="row g-4">
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Name</label><input class="form-control" name="name" required></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Display Name</label><input class="form-control" name="name_display" required></div>
            <div class="col-md-4"><label class="form-label required fw-medium text-dark">Component Type</label><select class="form-select" name="component_type"><option value="earning">Earning</option><option value="deduction">Deduction</option></select></div>
            <div class="col-md-4"><label class="form-label required fw-medium text-dark">Calculation Type</label><select class="form-select" name="calculation_type"><option value="fixed">Fixed</option><option value="percentage">Percentage</option></select></div>
            <div class="col-md-4"><label class="form-label required fw-medium text-dark">Value</label><input class="form-control" type="number" name="value" step="0.01" min="0" value="0" required></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Sort Order</label><input class="form-control" type="number" name="sort_order" min="0" value="0"></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            <div class="col-12"><label class="form-label fw-medium text-dark">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel 
        id="payGradeOffcanvas" 
        formId="payGradeForm" 
        title="Pay Grade"
        action="{{ route('admin.payroll.pay-grades.store') }}" 
        method="POST" 
        width="600px" 
        saveButtonText="Save"
    >
        <div class="row g-4">
            <div class="col-12"><label class="form-label required fw-medium text-dark">Name</label><input class="form-control" name="name" required></div>
            <div class="col-12"><label class="form-label fw-medium text-dark">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Min Salary</label><input class="form-control" type="number" name="min_salary" step="0.01" min="0"></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Max Salary</label><input class="form-control" type="number" name="max_salary" step="0.01" min="0"></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel 
        id="salaryStructureOffcanvas" 
        formId="salaryStructureForm" 
        title="Employee Salary Structure"
        action="{{ route('admin.payroll.salary-structures.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Save"
    >
        <div class="row g-4">
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Employee ID</label><input class="form-control" name="employee_id" required></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Employee Type</label><select class="form-select" name="employee_type"><option value="">Select</option><option value="teacher">Teacher</option><option value="staff">Staff</option></select></div>
            <div class="col-12"><label class="form-label fw-medium text-dark">Pay Grade</label><select class="form-select" name="pay_grade_id"><option value="">Select</option>@foreach($payGrades as $g)<option value="{{ $g->id }}">{{ $g->name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Effective From</label><input class="form-control" type="date" name="effective_from" required></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Effective To</label><input class="form-control" type="date" name="effective_to"></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Total CTC</label><input class="form-control" type="number" name="total_ctc" step="0.01" min="0" value="0" required></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            let runItemsTable = null;

            const tables = {
                departments: $('#departmentsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.payroll.departments.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'description'}, {data:'sort_order'}, {data:'designations_count', searchable:false}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                designations: $('#designationsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.payroll.designations.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'department_name', orderable:false, searchable:false}, {data:'description'}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                salaryComponents: $('#salaryComponentsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.payroll.salary-components.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'name_display'}, {data:'component_type'}, {data:'calculation_type'}, {data:'value'}, {data:'sort_order'}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                payGrades: $('#payGradesTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.payroll.pay-grades.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'description'}, {data:'min_salary'}, {data:'max_salary'}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                salaryStructures: $('#salaryStructuresTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.payroll.salary-structures.data') }}', columns: [
                    {data:'id'}, {data:'employee_name', orderable:false, searchable:false}, {data:'employee_type', orderable:false, searchable:false}, {data:'pay_grade_name', orderable:false, searchable:false}, {data:'effective_from'}, {data:'effective_to'}, {data:'total_ctc'}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                payrollRuns: $('#payrollRunsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.payroll.runs.data') }}', columns: [
                    {data:'id'}, {data:'period', orderable:false}, {data:'status'}, {data:'generated_at'}, {data:'items_count', searchable:false}, {data:'actions', orderable:false, searchable:false}
                ]})
            };
            initTabPersistence('#payrollTabs');

            const config = {
                department: {modal: '#departmentOffcanvas', form: '#departmentForm', store: '{{ route('admin.payroll.departments.store') }}', table: tables.departments},
                designation: {modal: '#designationOffcanvas', form: '#designationForm', store: '{{ route('admin.payroll.designations.store') }}', table: tables.designations},
                'salary-component': {modal: '#salaryComponentOffcanvas', form: '#salaryComponentForm', store: '{{ route('admin.payroll.salary-components.store') }}', table: tables.salaryComponents},
                'pay-grade': {modal: '#payGradeOffcanvas', form: '#payGradeForm', store: '{{ route('admin.payroll.pay-grades.store') }}', table: tables.payGrades},
                'salary-structure': {modal: '#salaryStructureOffcanvas', form: '#salaryStructureForm', store: '{{ route('admin.payroll.salary-structures.store') }}', table: tables.salaryStructures}
            };

            $('.open-modal').on('click', function () {
                const modalId = $(this).data('modal');
                const setup = Object.values(config).find(item => item.modal === modalId);
                const form = $(setup.form);
                form[0].reset();
                form.attr('action', setup.store);
                form.find('[name="_method"]').val('POST');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();

                bootstrap.Offcanvas.getOrCreateInstance(document.querySelector(modalId)).show();
            });

            $('#departmentForm, #designationForm, #salaryComponentForm, #payGradeForm, #salaryStructureForm').on('erp:success', function () {
                bootstrap.Offcanvas.getInstance($(this).closest('.offcanvas')[0]).hide();
                Object.values(tables).forEach(table => table.ajax.reload(null, false));
            });

            $(document).on('click', '.edit-payroll', function () {
                const type = $(this).data('type');
                const setup = config[type];
                const form = $(setup.form);
                $.get($(this).data('url'), (response) => {
                    form[0].reset();
                    form.attr('action', $(this).data('update-url'));
                    form.find('[name="_method"]').val('PUT');
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    Object.entries(response.data).forEach(([key, value]) => {
                        const field = form.find(`[name="${key}"]`);
                        if (field.attr('type') === 'checkbox') {
                            field.prop('checked', Boolean(value));
                        } else {
                            field.val(value);
                        }
                    });
                    form.find('select').trigger('change.select2');

                    bootstrap.Offcanvas.getOrCreateInstance(document.querySelector(setup.modal)).show();
                });
            });

            $(document).on('click', '.delete-payroll', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => Object.values(tables).forEach(table => table.ajax.reload(null, false))
                });
            });

            // Payroll Runs: Generate trigger
            $('#generatePayrollBtn').on('click', function() {
                const form = $('#generatePayrollForm');
                form[0].reset();
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();

                bootstrap.Offcanvas.getOrCreateInstance(document.querySelector('#generatePayrollOffcanvas')).show();
            });

            // Payroll Runs: Generate success
            $('#generatePayrollForm').on('erp:success', function () {
                bootstrap.Offcanvas.getInstance(document.querySelector('#generatePayrollOffcanvas')).hide();
                tables.payrollRuns.ajax.reload(null, false);
            });

            // Payroll Runs: View details
            $(document).on('click', '.view-run', function () {
                const btn = $(this);
                const url = btn.data('url');
                const itemsUrl = btn.data('items-url');

                $.get(url, (response) => {
                    const r = response.data;
                    $('#runSummary').html(`
                        <div class="col-md-3"><small class="text-muted fw-bold">Period</small><div class="fw-bold">${r.month_name} ${r.year}</div></div>
                        <div class="col-md-3"><small class="text-muted fw-bold">Status</small><div><span class="badge bg-${r.status === 'draft' ? 'warning-subtle text-warning' : 'success-subtle text-success'}">${r.status}</span></div></div>
                        <div class="col-md-3"><small class="text-muted fw-bold">Employees</small><div class="fw-bold">${r.items_count}</div></div>
                        <div class="col-md-3"><small class="text-muted fw-bold">Generated At</small><div>${r.generated_at ?? '-'}</div></div>
                    `);

                    if (runItemsTable) { runItemsTable.destroy(); }
                    runItemsTable = $('#runItemsTable').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: itemsUrl,
                        columns: [
                            {data:'employee_name', orderable:false},
                            {data:'employee_type', orderable:false},
                            {data:'gross_salary'},
                            {data:'total_deductions'},
                            {data:'net_salary'},
                            {data:'status'}
                        ]
                    });

                    bootstrap.Offcanvas.getOrCreateInstance(document.querySelector('#runDetailOffcanvas')).show();
                });
            });

            // Payroll Runs: Lock
            $(document).on('click', '.lock-run', function () {
                const btn = $(this);
                if (!confirm(`Lock payroll run for ${btn.data('period')}? This action cannot be undone.`)) return;
                $.ajax({
                    url: btn.data('url'),
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''},
                    success: function () {
                        tables.payrollRuns.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Failed to lock payroll run.';
                        alert(msg);
                    }
                });
            });
            // Payslips
            let payslipsTable = null;

            function initPayslipsTable(runId) {
                if (payslipsTable) { payslipsTable.destroy(); payslipsTable = null; }
                if (!runId) { $('#payslipsTable tbody').html('<tr><td colspan="8" class="text-center text-muted">Select a locked payroll run to view payslips.</td></tr>'); return; }
                payslipsTable = $('#payslipsTable').DataTable({
                    processing: true, serverSide: true, responsive: true, stateSave: false,
                    ajax: { url: '{{ route('admin.payroll.payslips.data') }}', data: d => { d.payroll_run_id = runId; }},
                    columns: [
                        {data:'payslip_number', orderable:false},
                        {data:'employee_name', orderable:false},
                        {data:'period', orderable:false},
                        {data:'gross_salary'},
                        {data:'total_deductions'},
                        {data:'net_salary'},
                        {data:'generated_at'},
                        {data:'actions', orderable:false, searchable:false}
                    ]
                });
            }

            $('#psFilterRun').on('change', function () {
                const runId = $(this).val();
                const selectedText = $(this).find('option:selected').text();
                const isLocked = selectedText.includes('locked');
                $('#bulkGenerateBtn').prop('disabled', !(runId && isLocked));
                initPayslipsTable(runId);
            });

            $('#bulkGenerateBtn').on('click', function () {
                const runId = $('#psFilterRun').val();
                if (!runId) return;
                if (!confirm('Generate payslips for all employees in this run? Already generated payslips will be skipped.')) return;
                $.ajax({
                    url: '{{ route('admin.payroll.payslips.bulk-generate') }}',
                    method: 'POST',
                    data: { payroll_run_id: runId, _token: document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                    success: function (response) {
                        if (payslipsTable) payslipsTable.ajax.reload(null, false);
                        if (response.message) App.notify?.(response.message, 'success');
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Failed to generate payslips.';
                        alert(msg);
                    }
                });
            });

            // Reset payslips table when tab changes
            $('#payrollTabs button[data-bs-target="#payslipsPane"]').on('shown.bs.tab', function () {
                if ($('#psFilterRun').val()) {
                    initPayslipsTable($('#psFilterRun').val());
                }
            });

        })(); });
    </script>
@endpush
