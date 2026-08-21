@extends('layouts.admin')

@section('title', 'Fees')
@section('page-title', 'Fees Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Fees</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="feesTabs" role="tablist">
                @foreach ([
                    'categories' => 'ti-category',
                    'structures' => 'ti-layout-list',
                    'assignments' => 'ti-clipboard-check',
                    'collections' => 'ti-cash',
                    'dues' => 'ti-alert-triangle',
                ] as $id => $icon)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link @if($loop->first) active @endif" data-bs-toggle="tab" data-bs-target="#{{ $id }}Pane" type="button"> {{ ucfirst($id === 'dues' ? 'Due Tracking' : ($id === 'collections' ? 'Collections' : ucfirst($id))) }}</button>
                    </li>
                @endforeach
                @can('fees.reports')
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('reports.fees.index') }}" class="nav-link" target="_blank"><i class="ti ti-external-link me-1"></i>View Fee Reports</a>
                    </li>
                @endcan
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="categoriesPane">
                    <div class="d-flex mb-3">
                        @can('fees.create')
                            <button type="button" class="btn btn-primary btn-sm ms-auto" id="createCategory">Add Category</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="categoriesTable">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Sort</th>
                            <th width="120">Actions</th>
                        </tr>
                        </thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="structuresPane">
                    <div class="d-flex mb-3">
                        @can('fees.create')
                            <button type="button" class="btn btn-primary btn-sm ms-auto" id="createStructure">Add Structure</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="structuresTable">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Year</th>
                            <th>Class</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                        </tr>
                        </thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="assignmentsPane">
                    <div class="d-flex flex-wrap gap-2 mb-3 justify-content-end">
                        @can('fees.create')
                            <button type="button" class="btn btn-outline-primary btn-sm" id="bulkAssignBtn">Bulk Assign</button>
                            <button type="button" class="btn btn-primary btn-sm" id="createAssign">Assign Student</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="assignmentsTable">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Admission</th>
                            <th>Year</th>
                            <th>Class</th>
                            <th>Total Due</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                        </tr>
                        </thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="collectionsPane">
                    <div class="d-flex mb-3">
                        @can('fees.collect')
                            <button type="button" class="btn btn-primary btn-sm ms-auto" id="openCollect">Collect Fees</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="collectionsTable">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Receipt</th>
                            <th>Student</th>
                            <th>Year</th>
                            <th>Amount</th>
                            <th>Mode</th>
                            <th>Paid On</th>
                            <th width="140">Actions</th>
                        </tr>
                        </thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="duesPane">
                    <p class="text-secondary small">Shows fee lines with a positive balance (pending or overdue).</p>
                    <table class="table table-striped table-bordered w-100" id="duesTable">
                        <thead>
                        <tr>
                            <th>Student</th>
                            <th>Admission</th>
                            <th>Year</th>
                            <th>Category</th>
                            <th>Due</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total:</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th colspan="2"></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="tab-pane fade" id="reportsPane">
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="ti ti-external-link" style="font-size:3rem;opacity:0.3;"></i>
                        </div>
                        <h5>Fee Reports Moved</h5>
                        <p class="text-secondary mb-4">Fee reports are now available under the main Reports section.</p>
                        <a href="{{ route('reports.fees.index') }}" class="btn btn-primary" target="_blank">
                            <i class="ti ti-external-link me-1"></i>Open Fee Reports Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <x-erp.side-panel
        id="categoryOffcanvas"
        formId="categoryForm"
        title="Fee Category"
        action="{{ route('admin.fees.categories.store') }}"
        method="POST"
        width="600px"
        saveButtonText="Save"
    >
        <input type="hidden" name="_method" value="POST" id="categoryMethod">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Category Details</h6>
        <div class="row g-4">
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Code</label>
                <input class="form-control" name="code" required maxlength="40">
            </div>
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Name</label>
                <input class="form-control" name="name" required maxlength="120">
            </div>
            <div class="col-12">
                <label class="form-label fw-medium text-dark">Description</label>
                <textarea class="form-control" name="description" rows="2" maxlength="500"></textarea>
            </div>
            <div class="col-12">
                <label class="form-label fw-medium text-dark">Sort order</label>
                <input class="form-control" type="number" name="sort_order" value="0" min="0">
            </div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel
        id="structureOffcanvas"
        formId="structureForm"
        title="Fee Structure"
        action="{{ route('admin.fees.structures.store') }}"
        method="POST"
        width="700px"
        saveButtonText="Save"
    >
        <input type="hidden" name="_method" value="POST" id="structureMethod">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Structure Details</h6>
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <label class="form-label required fw-medium text-dark">Academic Year</label>
                <select class="form-select" name="academic_year_id" required>
                    <option value="">Select</option>
                    @foreach ($academicYears as $y)
                        <option value="{{ $y->id }}">{{ $y->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label required fw-medium text-dark">Class & Section</label>
                <select class="form-select" name="class_section_id" required>
                    <option value="">Select</option>
                    @foreach ($classSections as $cs)
                        <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium text-dark">Label</label>
                <input class="form-control" name="name" maxlength="150" placeholder="Optional">
            </div>
            <div class="col-md-4">
                <label class="form-label required fw-medium text-dark">Status</label>
                <select class="form-select" name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="d-flex align-items-center mb-2">
            <h6 class="fw-bold text-uppercase text-muted mb-0" style="font-size: 0.75rem; letter-spacing: 0.5px;">Fee lines</h6>
            <button type="button" class="btn btn-sm btn-outline-primary ms-auto" id="addStructureRow">Add line</button>
        </div>
        <table class="table table-sm" id="structureItemsTable">
            <thead><tr><th>Category</th><th>Amount</th><th></th></tr></thead>
            <tbody id="structureItemsBody"></tbody>
        </table>
    </x-erp.side-panel>

    <x-erp.side-panel
        id="assignOffcanvas"
        formId="assignForm"
        title="Assign fees to student"
        action="{{ route('admin.fees.assignments.store') }}"
        method="POST"
        width="600px"
        saveButtonText="Assign"
    >
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Assignment Details</h6>
        <div class="row g-4">
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Student</label>
                <select class="form-select searchable-select" name="student_id" required data-ajax-url="{{ route('admin.students.search') }}" data-placeholder="Search student by name or admission no...">
                    <option value=""></option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Academic Year</label>
                <select class="form-select" name="academic_year_id" required>
                    @foreach ($academicYears as $y)
                        <option value="{{ $y->id }}">{{ $y->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Fee structure</label>
                <select class="form-select" name="fee_structure_id" required id="assignStructureSelect">
                    <option value="">Select class/year first</option>
                </select>
                <small class="text-secondary d-block mt-1">Choose the structure that matches the student's class for that year.</small>
            </div>
            <div class="col-12">
                <label class="form-label fw-medium text-dark">Default due date (all lines)</label>
                <input type="date" class="form-control" name="default_due_date">
            </div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel
        id="bulkAssignOffcanvas"
        formId="bulkAssignForm"
        title="Bulk assign (class)"
        action="{{ route('admin.fees.assignments.bulk') }}"
        method="POST"
        width="600px"
        saveButtonText="Assign Class"
    >
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Bulk Assignment Details</h6>
        <div class="row g-4">
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Academic Year</label>
                <select class="form-select" name="academic_year_id" required>
                    @foreach ($academicYears as $y)
                        <option value="{{ $y->id }}">{{ $y->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Class & Section</label>
                <select class="form-select" name="class_section_id" required>
                    @foreach ($classSections as $cs)
                        <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label required fw-medium text-dark">Fee structure</label>
                <select class="form-select" name="fee_structure_id" required id="bulkStructureSelect">
                    <option value="">Select year & class</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-medium text-dark">Default due date</label>
                <input type="date" class="form-control" name="default_due_date">
            </div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel
        id="editAssignOffcanvas"
        formId="editAssignForm"
        title="Edit assignment"
        action="#"
        method="POST"
        width="600px"
        saveButtonText="Update"
    >
        <input type="hidden" name="_method" value="PUT" id="editAssignMethod">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Assignment Details</h6>
        <div class="row g-4 mb-4">
            <div class="col-md-12">
                <label class="form-label required fw-medium text-dark">Status</label>
                <select class="form-select" name="status" id="editAssignStatus" required>
                    <option value="active">Active</option>
                    <option value="waived">Waived</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-12">
                <table class="table table-sm" id="editAssignItemsTable">
                    <thead><tr><th>Category</th><th>Amount</th><th>Due</th></tr></thead>
                    <tbody id="editAssignItemsBody"></tbody>
                </table>
            </div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel
        id="collectOffcanvas"
        formId="collectForm"
        title="Collect fees"
        action="{{ route('admin.fees.collections.store') }}"
        method="POST"
        width="700px"
        saveButtonText="Save Payment"
    >
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Payment Details</h6>
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Student</label>
                <select class="form-select searchable-select" name="student_id" id="collectStudentId" required data-ajax-url="{{ route('admin.students.search') }}" data-placeholder="Search student by name or admission no...">
                    <option value=""></option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Academic Year</label>
                <select class="form-select" name="academic_year_id" id="collectYearId" required>
                    @foreach ($academicYears as $y)
                        <option value="{{ $y->id }}">{{ $y->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Paid on</label>
                <input type="date" class="form-control" name="paid_on" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Mode</label>
                <select class="form-select" name="payment_mode" required>
                    @foreach ($paymentModes as $k => $label)
                        <option value="{{ $k }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label fw-medium text-dark">Remarks</label>
                <input class="form-control" name="remarks" maxlength="500">
            </div>
            <div class="col-12 mt-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="loadCollectLines">Load pending lines</button>
            </div>
        </div>
        <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Pending Lines</h6>
        <table class="table table-sm">
            <thead><tr><th>Category</th><th>Balance</th><th>Pay now</th></tr></thead>
            <tbody id="collectLinesBody"></tbody>
        </table>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const categoryOffcanvas = new bootstrap.Offcanvas(document.getElementById('categoryOffcanvas'));
            const structureOffcanvas = new bootstrap.Offcanvas(document.getElementById('structureOffcanvas'));
            const editAssignOffcanvas = new bootstrap.Offcanvas(document.getElementById('editAssignOffcanvas'));
            const collectOffcanvas = new bootstrap.Offcanvas(document.getElementById('collectOffcanvas'));
            const assignOffcanvas = new bootstrap.Offcanvas(document.getElementById('assignOffcanvas'));
            const bulkAssignOffcanvas = new bootstrap.Offcanvas(document.getElementById('bulkAssignOffcanvas'));

            const feeCategoryOptions = `@foreach($feeCategories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach`;

            function addStructureRow(categoryId = '', amount = '') {
                const i = $('#structureItemsBody tr').length;
                const tr = $(`<tr>
                    <td><select class="form-select form-select-sm" name="items[${i}][fee_category_id]" required>${feeCategoryOptions}</select></td>
                    <td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="items[${i}][amount]" required></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-structure-row">&times;</button></td>
                </tr>`);
                $('#structureItemsBody').append(tr);
                if (categoryId) tr.find('select').val(categoryId);
                if (amount !== '') tr.find('input[name$="[amount]"]').val(amount);
            }

            $('#addStructureRow').on('click', () => addStructureRow());

            $('#structureItemsBody').on('click', '.remove-structure-row', function () {
                $(this).closest('tr').remove();
                $('#structureItemsBody tr').each(function (idx) {
                    $(this).find('select').attr('name', `items[${idx}][fee_category_id]`);
                    $(this).find('input').attr('name', `items[${idx}][amount]`);
                });
            });

            $('#createCategory').on('click', () => {
                const form = $('#categoryForm');
                form[0].reset();
                $('#categoryMethod').val('POST');
                form.attr('action', '{{ route('admin.fees.categories.store') }}');
                $('#categoryOffcanvas').find('.offcanvas-title').text('Add Fee Category');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();

                categoryOffcanvas.show();
            });

            $('#categoriesTable').on('click', '.edit-fee-category', function () {
                const form = $('#categoryForm');
                $.get($(this).data('url'), (res) => {
                    form[0].reset();
                    form.attr('action', $(this).data('update-url'));
                    $('#categoryMethod').val('PUT');
                    $('#categoryOffcanvas').find('.offcanvas-title').text('Edit Fee Category');
                    Object.entries(res.data).forEach(([k, v]) => form.find(`[name="${k}"]`).val(v));
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();

                    categoryOffcanvas.show();
                });
            });

            $('#categoriesTable').on('click', '.delete-fee-category', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => tables.categories?.ajax.reload(null, false)
                });
            });

            $('#createStructure').on('click', () => {
                const form = $('#structureForm');
                form[0].reset();
                $('#structureMethod').val('POST');
                form.attr('action', '{{ route('admin.fees.structures.store') }}');
                $('#structureOffcanvas').find('.offcanvas-title').text('Add Fee Structure');
                $('#structureItemsBody').empty();
                addStructureRow();
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();

                structureOffcanvas.show();
            });

            $('#structuresTable').on('click', '.edit-fee-structure', function () {
                const form = $('#structureForm');
                $.get($(this).data('url'), (res) => {
                    form[0].reset();
                    form.attr('action', $(this).data('update-url'));
                    $('#structureMethod').val('PUT');
                    $('#structureOffcanvas').find('.offcanvas-title').text('Edit Fee Structure');
                    $('#structureItemsBody').empty();
                    const d = res.data;
                    form.find('[name="academic_year_id"]').val(d.academic_year_id);
                    form.find('[name="class_section_id"]').val(d.class_section_id);
                    form.find('[name="name"]').val(d.name);
                    form.find('[name="status"]').val(d.status);
                    (d.items || []).forEach((it) => addStructureRow(String(it.fee_category_id), it.amount));
                    if (!(d.items || []).length) addStructureRow();
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();

                    structureOffcanvas.show();
                });
            });

            $('#structuresTable').on('click', '.delete-fee-structure', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => tables.structures?.ajax.reload(null, false)
                });
            });

            const structuresIndex = @json($structuresForSelect);

            function refreshStructureSelects() {
                const ay = $('#assignForm [name="academic_year_id"]').val();
                const bulkAy = $('#bulkAssignForm [name="academic_year_id"]').val();
                const bulkCs = $('#bulkAssignForm [name="class_section_id"]').val();
                const assignSel = $('#assignStructureSelect').empty().append('<option value="">Select structure</option>');
                structuresIndex.forEach((s) => {
                    if (String(s.academic_year_id) === String(ay)) {
                        assignSel.append(`<option value="${s.id}">${s.label}</option>`);
                    }
                });
                const bulkSel = $('#bulkStructureSelect').empty().append('<option value="">Select structure</option>');
                structuresIndex.forEach((s) => {
                    if (String(s.academic_year_id) === String(bulkAy) && String(s.class_section_id) === String(bulkCs)) {
                        bulkSel.append(`<option value="${s.id}">${s.label}</option>`);
                    }
                });
            }

            $('#assignForm [name="academic_year_id"]').on('change', refreshStructureSelects);
            $('#bulkAssignForm [name="academic_year_id"], #bulkAssignForm [name="class_section_id"]').on('change', refreshStructureSelects);
            refreshStructureSelects();

            $('#createAssign').on('click', () => {
                const form = $('#assignForm');
                form[0].reset();
                form.find('select.searchable-select').val(null).trigger('change');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();

                assignOffcanvas.show();
            });

            $('#bulkAssignBtn').on('click', () => {
                const form = $('#bulkAssignForm');
                form[0].reset();
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();

                bulkAssignOffcanvas.show();
            });

            $('#assignmentsTable').on('click', '.edit-assignment', function () {
                const form = $('#editAssignForm');
                $.get($(this).data('url'), (res) => {
                    form.attr('action', $(this).data('update-url'));
                    $('#editAssignStatus').val(res.data.status);
                    const body = $('#editAssignItemsBody').empty();
                    (res.data.items || []).forEach((it, idx) => {
                        body.append(`<tr>
                            <td>${it.category_name}<input type="hidden" name="items[${idx}][id]" value="${it.id}"></td>
                            <td><input class="form-control form-control-sm" name="items[${idx}][amount]" value="${it.amount}"></td>
                            <td><input class="form-control form-control-sm" type="date" name="items[${idx}][due_date]" value="${it.due_date || ''}"></td>
                        </tr>`);
                    });
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();

                    editAssignOffcanvas.show();
                });
            });

            $('#assignmentsTable').on('click', '.delete-assignment', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => tables.assignments?.ajax.reload(null, false)
                });
            });

            $('#openCollect').on('click', () => {
                const form = $('#collectForm');
                form[0].reset();
                form.find('select.searchable-select').val(null).trigger('change');
                $('#collectLinesBody').empty();
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();

                collectOffcanvas.show();
            });

            $('#loadCollectLines').on('click', () => {
                const sid = $('#collectStudentId').val();
                const yid = $('#collectYearId').val();
                if (!sid || !yid) {
                    App.toast('error', 'Select student and academic year.');
                    return;
                }
                $.get('{{ route('admin.fees.api.student-fee-items') }}', {student_id: sid, academic_year_id: yid}, (res) => {
                    const body = $('#collectLinesBody').empty();
                    let i = 0;
                    (res.data || []).forEach((line) => {
                        if (line.balance <= 0) return;
                        body.append(`<tr>
                            <td>${line.category}<input type="hidden" name="lines[${i}][student_fee_item_id]" value="${line.id}"></td>
                            <td>${Number(line.balance).toFixed(2)}</td>
                            <td><input class="form-control form-control-sm" type="number" step="0.01" min="0" max="${line.balance}" name="lines[${i}][amount]" placeholder="0"></td>
                        </tr>`);
                        i++;
                    });
                    if (!i) App.toast('info', 'No pending fee lines for this student and year.');
                });
            });

            $('#collectionsTable').on('click', '.void-collection', function () {
                const url = $(this).data('url');
                const reason = prompt('Enter a reason for voiding this payment (minimum 5 characters):');
                if (reason === null) return;
                if (!reason || reason.trim().length < 5) {
                    App.toast('error', 'A reason of at least 5 characters is required to void a payment.');
                    return;
                }
                $.post(url, {reason: reason.trim()}, () => {
                    App.toast('success', 'Payment voided.');
                    tables.collections?.ajax.reload(null, false);
                }).fail((xhr) => {
                    App.toast('error', xhr.responseJSON?.message || 'Unable to void payment.');
                });
            });

            function createFeeTable(selector, opts) {
                try {
                    opts.error = function(xhr, error, thrown) {
                        console.error('[Fee DT] ' + selector + ' error:', error, thrown, xhr.responseJSON);
                    };
                    return $(selector).DataTable(opts);
                } catch (e) {
                    console.error('[Fee DT] ' + selector + ' failed to initialize:', e);
                    return null;
                }
            }
            const tables = {
                categories: createFeeTable('#categoriesTable', {
                    processing: true, serverSide: true, responsive: true, stateSave: true,
                    ajax: '{{ route('admin.fees.categories.data') }}',
                    columns: [
                        {data: 'id'}, {data: 'code'}, {data: 'name'}, {data: 'sort_order'},
                        {data: 'actions', orderable: false, searchable: false}
                    ]
                }),
                structures: createFeeTable('#structuresTable', {
                    processing: true, serverSide: true, responsive: true, stateSave: true,
                    ajax: '{{ route('admin.fees.structures.data') }}',
                    columns: [
                        {data: 'id'}, {data: 'name'}, {data: 'academic_year', orderable: false, searchable: false},
                        {data: 'class_section', orderable: false, searchable: false},
                        {data: 'totals', orderable: false, searchable: false},
                        {data: 'status', orderable: false, searchable: false},
                        {data: 'actions', orderable: false, searchable: false}
                    ]
                }),
                assignments: createFeeTable('#assignmentsTable', {
                    processing: true, serverSide: true, responsive: true, stateSave: true,
                    ajax: '{{ route('admin.fees.assignments.data') }}',
                    columns: [
                        {data: 'id'}, {data: 'student', orderable: false, searchable: false},
                        {data: 'admission_no', orderable: false, searchable: false},
                        {data: 'academic_year', orderable: false, searchable: false},
                        {data: 'class_section', orderable: false, searchable: false},
                        {data: 'total_due', orderable: false, searchable: false},
                        {data: 'status'}, {data: 'actions', orderable: false, searchable: false}
                    ]
                }),
                collections: createFeeTable('#collectionsTable', {
                    processing: true, serverSide: true, responsive: true, stateSave: true,
                    ajax: '{{ route('admin.fees.collections.data') }}',
                    columns: [
                        {data: 'id'}, {data: 'receipt_number'}, {data: 'student', orderable: false, searchable: false},
                        {data: 'academic_year', orderable: false, searchable: false},
                        {data: 'amount'}, {data: 'mode_label', orderable: false, searchable: false},
                        {data: 'paid_on'}, {data: 'status_label', orderable: false, searchable: false},
                        {data: 'actions', orderable: false, searchable: false}
                    ]
                }),
                dues: createFeeTable('#duesTable', {
                    processing: true, serverSide: false, responsive: true, stateSave: true,
                    ajax: '{{ route('admin.fees.dues.data') }}',
                    columns: [
                        {data: 'student_name'}, {data: 'admission_no'}, {data: 'academic_year'},
                        {data: 'category'}, {data: 'amount'}, {data: 'paid'}, {data: 'balance'},
                        {data: 'due_date'}, {data: 'overdue_badge', orderable: false, searchable: false}
                    ],
                    footerCallback: function (row, data, start, end, display) {
                        var api = this.api();
                        var intVal = function (i) {
                            return typeof i === 'string' ? i.replace(/[\$,₹,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                        };
                        var dueTotal = api.column(4, {search: 'applied'}).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
                        var paidTotal = api.column(5, {search: 'applied'}).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
                        var balanceTotal = api.column(6, {search: 'applied'}).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
                        $(api.column(4).footer()).html('₹ ' + dueTotal);
                        $(api.column(5).footer()).html('₹ ' + paidTotal);
                        $(api.column(6).footer()).html('₹ ' + balanceTotal);
                    }
                })
            };
            initTabPersistence('#feesTabs');

            $('.fees-category-form').on('erp:success', () => {
                categoryOffcanvas.hide();
                tables.categories?.ajax.reload(null, false);
            });

            $('.fees-structure-form').on('erp:success', () => {
                structureOffcanvas.hide();
                tables.structures?.ajax.reload(null, false);
                window.location.reload();
            });

            $('#assignForm, #bulkAssignForm').on('erp:success', () => {
                assignOffcanvas.hide();
                bulkAssignOffcanvas.hide();
                tables.assignments?.ajax.reload(null, false);
            });

            $('#editAssignForm').on('erp:success', () => {
                editAssignOffcanvas.hide();
                tables.assignments?.ajax.reload(null, false);
            });

            $('#collectForm').on('erp:success', () => {
                collectOffcanvas.hide();
                tables.collections?.ajax.reload(null, false);
                tables.dues?.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush
