@extends('layouts.admin')

@section('title', 'Employee Documents')
@section('page-title', 'Employee Documents')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.hr.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">Documents</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title fw-semibold mb-0"><i class="ti ti-file-text text-primary me-2"></i>Employee Documents</h3>
            @can('hr.create')
                <button class="btn btn-primary btn-sm ms-auto" id="createDocument">Upload Document</button>
            @endcan
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered w-100" id="documentsTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee</th>
                    <th>Document Type</th>
                    <th>Document Name</th>
                    <th>Status</th>
                    <th>Verified At</th>
                    <th width="150">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <x-erp.side-panel 
        id="documentOffcanvas" 
        formId="documentForm" 
        title="Upload Document"
        action="{{ route('admin.hr.documents.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Upload"
        :hasTabs="false"
        :multipart="true"
    >
        <input type="hidden" name="_method" value="POST" id="documentMethod">
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Employee</label>
                <select class="form-select" name="employee_id" required>
                    <option value="">Select Employee</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Document Type</label>
                <select class="form-select" name="document_type" required>
                    <option value="id_proof">ID Proof</option>
                    <option value="qualification">Qualification</option>
                    <option value="appointment_letter">Appointment Letter</option>
                    <option value="experience_certificate">Experience Certificate</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Document Name</label>
                <input class="form-control" name="document_name" required maxlength="200">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium text-dark">Document Number</label>
                <input class="form-control" name="document_number" maxlength="100">
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">File</label>
                <input class="form-control" type="file" name="file" required accept="image/png,image/jpeg,image/webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
            </div>
            <div class="col-12">
                <label class="form-label fw-medium text-dark">Remarks</label>
                <textarea class="form-control" name="remarks" rows="3" maxlength="1000"></textarea>
            </div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('documentOffcanvas'));
            const form = $('#documentForm');
            const table = $('#documentsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.hr.documents.data') }}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'employee_name', name: 'employee.first_name'},
                    {data: 'document_type', name: 'document_type'},
                    {data: 'document_name', name: 'document_name'},
                    {data: 'status_badge', name: 'status', orderable: false, searchable: false},
                    {data: 'verified_at', name: 'verified_at'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ]
            });

            $('#createDocument').on('click', () => {
                form[0].reset();
                $('#documentMethod').val('POST');
                form.attr('action', '{{ route('admin.hr.documents.store') }}');
                $('#documentOffcanvasTitle').text('Upload Document');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
                
                form.find('select').trigger('change.select2');

                offcanvas.show();
            });

            $('#documentsTable').on('click', '.verify-document', function () {
                App.confirmAction({
                    url: $(this).data('url'),
                    method: 'POST',
                    onSuccess: () => table.ajax.reload(null, false),
                });
            });

            $('#documentsTable').on('click', '.edit-document', function () {
                $.get($(this).data('url'), (response) => {
                    form[0].reset();
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    form.attr('action', $(this).data('update-url'));
                    $('#documentMethod').val('PUT');
                    $('#documentOffcanvasTitle').text('Edit Document');

                    Object.entries(response.data).forEach(([key, value]) => {
                        const field = form.find(`[name="${key}"]`);
                        if (field.length && key !== 'file') {
                            field.val(value);
                        }
                    });

                    form.find('select').trigger('change.select2');

                    offcanvas.show();
                });
            });

            $('#documentsTable').on('click', '.delete-document', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false),
                });
            });

            form.on('erp:success', () => {
                offcanvas.hide();
                table.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush
