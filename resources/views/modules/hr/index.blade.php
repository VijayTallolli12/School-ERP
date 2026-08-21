@extends('layouts.admin')

@section('title', 'HR Management')
@section('page-title', 'Employee Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Employees</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title fw-semibold mb-0"><i class="ti ti-users text-primary me-2"></i>Employees</h3>
            @can('hr.create')
                <button class="btn btn-primary btn-sm ms-auto" id="createEmployee">Add Employee</button>
            @endcan
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered w-100" id="employeesTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee Code</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Status</th>
                    <th width="120">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <x-erp.side-panel 
        id="employeeOffcanvas" 
        formId="employeeForm" 
        title="Add Employee"
        action="{{ route('admin.hr.store') }}" 
        method="POST" 
        width="1000px" 
        saveButtonText="Save Employee"
        :hasTabs="true"
        :multipart="true"
    >
        <x-slot name="tabs">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-medium px-4" data-bs-toggle="tab" data-bs-target="#employeeBasic" type="button">Basic</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-medium px-4" data-bs-toggle="tab" data-bs-target="#employeeContact" type="button">Contact</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-medium px-4" data-bs-toggle="tab" data-bs-target="#employeeBank" type="button">Bank & Statutory</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-medium px-4" data-bs-toggle="tab" data-bs-target="#employeeEmployment" type="button">Employment</button>
                </li>
            </ul>
        </x-slot>

        <input type="hidden" name="_method" value="POST" id="employeeMethod">
        <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="employeeBasic">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Basic Details</h6>
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label required fw-medium text-dark">Employee Code</label>
                        <input class="form-control" name="employee_code" required maxlength="50">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required fw-medium text-dark">First Name</label>
                        <input class="form-control" name="first_name" required maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Middle Name</label>
                        <input class="form-control" name="middle_name" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Last Name</label>
                        <input class="form-control" name="last_name" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Date of Birth</label>
                        <input class="form-control" type="date" name="date_of_birth">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Gender</label>
                        <select class="form-select" name="gender">
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Marital Status</label>
                        <select class="form-select" name="marital_status">
                            <option value="">Select</option>
                            <option value="single">Single</option>
                            <option value="married">Married</option>
                            <option value="divorced">Divorced</option>
                            <option value="widowed">Widowed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Blood Group</label>
                        <input class="form-control" name="blood_group" maxlength="10">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Nationality</label>
                        <input class="form-control" name="nationality" maxlength="50">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Religion</label>
                        <input class="form-control" name="religion" maxlength="50">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Profile Image</label>
                        <input class="form-control" type="file" name="profile_image" accept="image/png,image/jpeg,image/webp">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="employeeContact">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Contact Details</h6>
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Email</label>
                        <input class="form-control" type="email" name="email" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Phone</label>
                        <input class="form-control" name="phone" maxlength="30">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Emergency Contact Name</label>
                        <input class="form-control" name="emergency_contact_name" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Emergency Contact Phone</label>
                        <input class="form-control" name="emergency_contact_phone" maxlength="30">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-dark">Address Line 1</label>
                        <input class="form-control" name="address_line1" maxlength="500">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-dark">Address Line 2</label>
                        <input class="form-control" name="address_line2" maxlength="500">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium text-dark">City</label>
                        <input class="form-control" name="city" maxlength="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium text-dark">State</label>
                        <input class="form-control" name="state" maxlength="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium text-dark">Zip Code</label>
                        <input class="form-control" name="zip_code" maxlength="20">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium text-dark">Country</label>
                        <input class="form-control" name="country" maxlength="100">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="employeeBank">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Bank & Statutory Details</h6>
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Bank Name</label>
                        <input class="form-control" name="bank_name" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Bank Account No.</label>
                        <input class="form-control" name="bank_account_no" maxlength="50">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Bank IFSC Code</label>
                        <input class="form-control" name="bank_ifsc_code" maxlength="20">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">PAN Number</label>
                        <input class="form-control" name="pan_number" maxlength="20">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">UAN Number</label>
                        <input class="form-control" name="uan_number" maxlength="20">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">PF Number</label>
                        <input class="form-control" name="pf_number" maxlength="20">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">ESI Number</label>
                        <input class="form-control" name="esi_number" maxlength="20">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="employeeEmployment">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Employment Details</h6>
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Date of Joining</label>
                        <input class="form-control" type="date" name="date_of_joining">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Date of Leaving</label>
                        <input class="form-control" type="date" name="date_of_leaving">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required fw-medium text-dark">Employment Type</label>
                        <select class="form-select" name="employment_type" required>
                            <option value="permanent">Permanent</option>
                            <option value="contract">Contract</option>
                            <option value="probationary">Probationary</option>
                            <option value="temporary">Temporary</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required fw-medium text-dark">Employment Status</label>
                        <select class="form-select" name="employment_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="terminated">Terminated</option>
                            <option value="resigned">Resigned</option>
                            <option value="retired">Retired</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Department</label>
                        <select class="form-select" name="department_id">
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Designation</label>
                        <select class="form-select" name="designation_id">
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-dark">Reporting To</label>
                        <select class="form-select" name="reporting_to_id">
                            <option value="">Select</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('employeeOffcanvas'));
            const form = $('#employeeForm');
            const table = $('#employeesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.hr.data') }}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'employee_code', name: 'employee_code'},
                    {data: 'full_name', name: 'first_name'},
                    {data: 'department', name: 'department', orderable: false, searchable: false},
                    {data: 'designation', name: 'designation', orderable: false, searchable: false},
                    {data: 'employment_status', name: 'employment_status'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ]
            });

            $('#createEmployee').on('click', () => {
                form[0].reset();
                $('#employeeMethod').val('POST');
                form.attr('action', '{{ route('admin.hr.store') }}');
                $('#employeeOffcanvasTitle').text('Add Employee');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
                
                form.find('select').trigger('change.select2');

                offcanvas.show();
            });

            $('#employeesTable').on('click', '.edit-employee', function () {
                $.get($(this).data('url'), (response) => {
                    form[0].reset();
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    form.attr('action', $(this).data('update-url'));
                    $('#employeeMethod').val('PUT');
                    $('#employeeOffcanvasTitle').text('Edit Employee');

                    Object.entries(response.data).forEach(([key, value]) => {
                        const field = form.find(`[name="${key}"]`);
                        if (field.length) {
                            field.val(value);
                        }
                    });

                    form.find('select').trigger('change.select2');

                    offcanvas.show();
                });
            });

            $('#employeesTable').on('click', '.delete-employee', function () {
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
