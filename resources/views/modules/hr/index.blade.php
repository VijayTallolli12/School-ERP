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
                <button class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#employeeModal" id="createEmployee">
                    <i class="ti ti-plus me-1"></i> Add Employee
                </button>
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
    <div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="employeeForm" method="POST" action="{{ route('admin.hr.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="POST" id="employeeMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalTitle">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#employeeBasic" type="button">Basic</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#employeeContact" type="button">Contact</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#employeeBank" type="button">Bank & Statutory</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#employeeEmployment" type="button">Employment</button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="employeeBasic">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required">Employee Code</label>
                                    <input class="form-control" name="employee_code" required maxlength="50">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">First Name</label>
                                    <input class="form-control" name="first_name" required maxlength="100">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Middle Name</label>
                                    <input class="form-control" name="middle_name" maxlength="100">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Last Name</label>
                                    <input class="form-control" name="last_name" maxlength="100">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Birth</label>
                                    <input class="form-control" type="date" name="date_of_birth">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender">
                                        <option value="">Select</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Marital Status</label>
                                    <select class="form-select" name="marital_status">
                                        <option value="">Select</option>
                                        <option value="single">Single</option>
                                        <option value="married">Married</option>
                                        <option value="divorced">Divorced</option>
                                        <option value="widowed">Widowed</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Blood Group</label>
                                    <input class="form-control" name="blood_group" maxlength="10">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nationality</label>
                                    <input class="form-control" name="nationality" maxlength="50">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Religion</label>
                                    <input class="form-control" name="religion" maxlength="50">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Profile Image</label>
                                    <input class="form-control" type="file" name="profile_image" accept="image/png,image/jpeg,image/webp">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="employeeContact">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Email</label>
                                    <input class="form-control" type="email" name="email" maxlength="255">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Phone</label>
                                    <input class="form-control" name="phone" maxlength="30">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Emergency Contact Name</label>
                                    <input class="form-control" name="emergency_contact_name" maxlength="100">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Emergency Contact Phone</label>
                                    <input class="form-control" name="emergency_contact_phone" maxlength="30">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Address Line 1</label>
                                    <input class="form-control" name="address_line1" maxlength="500">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Address Line 2</label>
                                    <input class="form-control" name="address_line2" maxlength="500">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">City</label>
                                    <input class="form-control" name="city" maxlength="100">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">State</label>
                                    <input class="form-control" name="state" maxlength="100">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Zip Code</label>
                                    <input class="form-control" name="zip_code" maxlength="20">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Country</label>
                                    <input class="form-control" name="country" maxlength="100">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="employeeBank">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bank Name</label>
                                    <input class="form-control" name="bank_name" maxlength="100">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Bank Account No.</label>
                                    <input class="form-control" name="bank_account_no" maxlength="50">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Bank IFSC Code</label>
                                    <input class="form-control" name="bank_ifsc_code" maxlength="20">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PAN Number</label>
                                    <input class="form-control" name="pan_number" maxlength="20">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">UAN Number</label>
                                    <input class="form-control" name="uan_number" maxlength="20">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PF Number</label>
                                    <input class="form-control" name="pf_number" maxlength="20">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">ESI Number</label>
                                    <input class="form-control" name="esi_number" maxlength="20">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="employeeEmployment">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Date of Joining</label>
                                    <input class="form-control" type="date" name="date_of_joining">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Leaving</label>
                                    <input class="form-control" type="date" name="date_of_leaving">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">Employment Type</label>
                                    <select class="form-select" name="employment_type" required>
                                        <option value="permanent">Permanent</option>
                                        <option value="contract">Contract</option>
                                        <option value="probationary">Probationary</option>
                                        <option value="temporary">Temporary</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">Employment Status</label>
                                    <select class="form-select" name="employment_status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="terminated">Terminated</option>
                                        <option value="resigned">Resigned</option>
                                        <option value="retired">Retired</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Department</label>
                                    <select class="form-select" name="department_id">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Designation</label>
                                    <select class="form-select" name="designation_id">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Reporting To</label>
                                    <select class="form-select" name="reporting_to_id">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ti ti-x me-1"></i>Cancel</button>
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-device-floppy me-1"></i> Save Employee</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const modal = new bootstrap.Modal('#employeeModal');
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
                $('#employeeModalTitle').text('Add Employee');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
            });

            $('#employeesTable').on('click', '.edit-employee', function () {
                $.get($(this).data('url'), (response) => {
                    form[0].reset();
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    form.attr('action', $(this).data('update-url'));
                    $('#employeeMethod').val('PUT');
                    $('#employeeModalTitle').text('Edit Employee');

                    Object.entries(response.data).forEach(([key, value]) => {
                        const field = form.find(`[name="${key}"]`);
                        if (field.length) {
                            field.val(value);
                        }
                    });

                    modal.show();
                });
            });

            $('#employeesTable').on('click', '.delete-employee', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false),
                });
            });

            form.on('erp:success', () => {
                modal.hide();
                table.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush
