@extends('layouts.admin')

@section('title', 'Transport Management')
@section('page-title', 'Transport Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Transport</li>
@endsection

@section('content')
    <div class="row g-3 mb-4" id="transportStats">
        <x-erp.stat-card label="Routes" :value="$stats['routes']" icon="route-2" color="info" cols="col-xl flex-fill" />
        <x-erp.stat-card label="Vehicles" :value="$stats['vehicles']" icon="bus" color="primary" cols="col-xl flex-fill" />
        <x-erp.stat-card label="Drivers" :value="$stats['drivers']" icon="user" color="warning" cols="col-xl flex-fill" />
        <x-erp.stat-card label="Assigned Students" :value="$stats['assigned_students']" icon="users" color="success" cols="col-xl flex-fill" />
        <x-erp.stat-card label="Avg Occupancy" :value="($stats['avg_occupancy'] ?? 'N/A') . '%'" icon="chart-bar" color="secondary" cols="col-xl flex-fill" />
    </div>

    <div class="card">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="transportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#vehiclesPane" type="button">Vehicles</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#driversPane" type="button">Drivers</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#routesPane" type="button">Routes</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#routeStopsPane" type="button">Route Stops</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#assignmentsPane" type="button">Assignments</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="vehiclesPane">
                    <div class="d-flex mb-3">
                        @can('transport.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#vehicleOffcanvas">Add Vehicle</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="vehiclesTable">
                        <thead><tr><th>ID</th><th>Number</th><th>Name</th><th>Type</th><th>Capacity</th><th>Driver</th><th>Attendant</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="driversPane">
                    <div class="d-flex mb-3">
                        @can('transport.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#driverOffcanvas">Add Driver</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="driversTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Mobile</th><th>License No</th><th>License Expiry</th><th>Status</th><th>Login</th><th>Vehicles</th><th width="140">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="routesPane">
                    <div class="d-flex mb-3">
                        @can('transport.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#routeOffcanvas">Add Route</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="routesTable">
                        <thead><tr><th>ID</th><th>Route Name</th><th>Start</th><th>End</th><th>Distance</th><th>Vehicle</th><th>Driver</th><th>Stops</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="routeStopsPane">
                    <div class="d-flex mb-3">
                        @can('transport.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#routeStopOffcanvas">Add Route Stop</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="routeStopsTable">
                        <thead><tr><th>ID</th><th>Route</th><th>Stop Name</th><th>Pickup Time</th><th>Drop Time</th><th>Sequence</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="assignmentsPane">
                    <div class="d-flex mb-3">
                        @can('transport.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#assignmentOffcanvas">Assign Student</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="assignmentsTable">
                        <thead><tr><th width="60">ID</th><th>Student</th><th>Route</th><th>Stop</th><th width="100">Pickup Time</th><th width="100">Drop Time</th><th>Vehicle</th><th>Pickup Point</th><th width="120">Monthly Fee</th><th width="90">Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <x-erp.side-panel 
        id="vehicleOffcanvas" 
        formId="vehicleForm" 
        formClass="transport-form"
        title="Vehicle"
        action="{{ route('admin.transport.vehicles.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Save"
    >
        <input type="hidden" name="_method" value="POST">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Vehicle Details</h6>
        <div class="row g-4">
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Vehicle Number</label><input class="form-control" name="vehicle_number" required></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Vehicle Name</label><input class="form-control" name="vehicle_name" required></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Type</label><select class="form-select" name="vehicle_type" required>
                <option value="bus">Bus</option><option value="van">Van</option><option value="car">Car</option><option value="other">Other</option>
            </select></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Capacity</label><input class="form-control" type="number" name="capacity" min="1" required></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Driver</label><select class="form-select" name="driver_id"><option value="">Select</option>@foreach($drivers as $driver)<option value="{{ $driver->id }}">{{ $driver->name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Attendant</label><input class="form-control" name="attendant"></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel 
        id="driverOffcanvas" 
        formId="driverForm" 
        formClass="transport-form"
        title="Driver"
        action="{{ route('admin.transport.drivers.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Save"
    >
        <input type="hidden" name="_method" value="POST">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Driver Details</h6>
        <div class="row g-4">
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Name</label><input class="form-control" name="name" required></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Mobile</label><input class="form-control" name="mobile" required></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">License Number</label><input class="form-control" name="license_number" required></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">License Expiry</label><input class="form-control" type="date" name="license_expiry_date" required></div>
            <div class="col-12"><label class="form-label fw-medium text-dark">Address</label><textarea class="form-control" name="address" rows="2"></textarea></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>

            <div class="col-12 border-top pt-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="enable_login" value="1" id="enableDriverLogin">
                    <label class="form-check-label fw-medium text-dark" for="enableDriverLogin">
                        <i class="ti ti-key me-1"></i> Enable Driver Login
                    </label>
                </div>
            </div>
            <div class="col-12" id="driverLoginFields" style="display:none;" data-email-label="Email">
                <div class="row g-4">
                    <div class="col-12"><label class="form-label required fw-medium text-dark" id="driverEmailLabel">Email</label><input class="form-control" type="email" name="email" id="driverEmail" maxlength="255"></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark" id="driverPasswordLabel">Password</label><input class="form-control" type="password" name="password" id="driverPassword" autocomplete="new-password"></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark" id="driverPasswordConfirmLabel">Confirm Password</label><input class="form-control" type="password" name="password_confirmation" id="driverPasswordConfirm" autocomplete="new-password"></div>
                    <div class="col-12"><div class="form-text" id="driverPasswordHint">New password. Leave blank to keep the current password.</div></div>
                </div>
            </div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel 
        id="routeOffcanvas" 
        formId="routeForm" 
        formClass="transport-form"
        title="Route"
        action="{{ route('admin.transport.routes.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Save"
    >
        <input type="hidden" name="_method" value="POST">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Route Details</h6>
        <div class="row g-4">
            <div class="col-12"><label class="form-label required fw-medium text-dark">Route Name</label><input class="form-control" name="route_name" required></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Start Point</label><input class="form-control" name="start_point" required></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">End Point</label><input class="form-control" name="end_point" required></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Distance (km)</label><input class="form-control" type="number" name="distance" step="0.01" min="0"></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Vehicle</label><select class="form-select" name="vehicle_id"><option value="">Select</option>@foreach($vehicles as $vehicle)<option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_number }} - {{ $vehicle->vehicle_name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Driver</label><select class="form-select" name="driver_id"><option value="">Select</option>@foreach($drivers as $driver)<option value="{{ $driver->id }}">{{ $driver->name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel 
        id="routeStopOffcanvas" 
        formId="routeStopForm" 
        formClass="transport-form"
        title="Route Stop"
        action="{{ route('admin.transport.route-stops.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Save"
    >
        <input type="hidden" name="_method" value="POST">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Stop Details</h6>
        <div class="row g-4">
            <div class="col-12"><label class="form-label required fw-medium text-dark">Route</label><select class="form-select" name="route_id" required><option value="">Select</option>@foreach($routes as $route)<option value="{{ $route->id }}">{{ $route->route_name }}</option>@endforeach</select></div>
            <div class="col-12"><label class="form-label required fw-medium text-dark">Stop Name</label><input class="form-control" name="stop_name" required></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Pickup Time</label><input class="form-control" type="time" name="pickup_time"></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Drop Time</label><input class="form-control" type="time" name="drop_time"></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Sequence</label><input class="form-control" type="number" name="sequence" min="0" value="0" required><div class="form-text">Controls pickup order. Drop order is automatically reverse of pickup.</div></div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel 
        id="assignmentOffcanvas" 
        formId="assignmentForm" 
        formClass="transport-form"
        title="Transport Assignment"
        action="{{ route('admin.transport.assignments.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Save"
    >
        <input type="hidden" name="_method" value="POST">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Assignment Details</h6>
        <div class="row g-4">
            <div class="col-12"><label class="form-label required fw-medium text-dark">Student</label><select class="form-select searchable-select" name="student_id" required data-ajax-url="{{ route('admin.transport.search.students') }}" data-placeholder="Search student by name or admission no..."><option value=""></option></select></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Route</label><select class="form-select searchable-select" name="route_id" id="assignmentRoute" data-placeholder="Select Route"><option value="">Select</option>@foreach($routes as $route)<option value="{{ $route->id }}" data-vehicle="{{ $route->vehicle_id ?? '' }}">{{ $route->route_name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Stop</label><select class="form-select searchable-select" name="route_stop_id" id="assignmentStop" data-placeholder="Select Route First"><option value="">Select Route First</option></select></div>
            <div class="col-md-3"><label class="form-label fw-medium text-dark">Pickup Time</label><input class="form-control" type="text" id="assignmentPickupTime" readonly></div>
            <div class="col-md-3"><label class="form-label fw-medium text-dark">Drop Time</label><input class="form-control" type="text" id="assignmentDropTime" readonly></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Vehicle</label><select class="form-select" name="vehicle_id" id="assignmentVehicle"><option value="">Select</option>@foreach($vehicles as $vehicle)<option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_number }} ({{ $vehicle->vehicle_name }})</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Pickup Point</label><input class="form-control" name="pickup_point"></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Monthly Fee</label><input class="form-control" type="number" name="monthly_fee" step="0.01" min="0" value="0"></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
    </x-erp.side-panel>

    <div class="modal fade" id="routeDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Route Detail</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body">
                    <div id="routeDetailContent" class="row g-3"></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Close</button></div>
            </div>
        </div>
    </div>

    <x-erp.side-panel 
        id="driverResetPasswordOffcanvas" 
        formId="driverResetPasswordForm" 
        formClass="transport-form"
        title="Reset Driver Password"
        action="" 
        method="POST" 
        width="400px" 
        saveButtonText="Reset Password"
    >
        <input type="hidden" name="_method" value="PUT">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Password Reset</h6>
        <p class="text-muted small">Resetting password for: <strong id="driverResetPasswordName">—</strong></p>
        <div class="row g-4">
            <div class="col-12"><label class="form-label required fw-medium text-dark">New Password</label><input class="form-control" type="password" name="password" required autocomplete="new-password"></div>
            <div class="col-12"><label class="form-label required fw-medium text-dark">Confirm Password</label><input class="form-control" type="password" name="password_confirmation" required autocomplete="new-password"></div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        const ROUTE_STOPS = @json($routeStopsJson);
        const ROUTES = @json($routesJson);
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const tables = {
                vehicles: $('#vehiclesTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.transport.vehicles.data') }}', columns: [
                    {data:'id'}, {data:'vehicle_number'}, {data:'vehicle_name'}, {data:'vehicle_type'}, {data:'capacity'}, {data:'driver_name', orderable:false, searchable:false}, {data:'attendant'}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                drivers: $('#driversTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.transport.drivers.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'mobile'}, {data:'license_number'}, {data:'license_expiry_date'}, {data:'status'}, {data:'login', orderable:false, searchable:false}, {data:'vehicles_count', searchable:false}, {data:'actions', orderable:false, searchable:false}
                ]}),
                routes: $('#routesTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.transport.routes.data') }}', columns: [
                    {data:'id'}, {data:'route_name'}, {data:'start_point'}, {data:'end_point'}, {data:'distance'}, {data:'vehicle_name', orderable:false, searchable:false}, {data:'driver_name', orderable:false, searchable:false}, {data:'stops_count', searchable:false}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                routeStops: $('#routeStopsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.transport.route-stops.data') }}', columns: [
                    {data:'id'}, {data:'route_name', orderable:false}, {data:'stop_name'}, {data:'pickup_time'}, {data:'drop_time'}, {data:'sequence'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                assignments: $('#assignmentsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.transport.assignments.data') }}', columns: [
                    {data:'id', width:'60px'}, {data:'student_name', orderable:false, width:'180px'}, {data:'route_name', orderable:false}, {data:'stop_name', orderable:false, searchable:false}, {data:'pickup_time', orderable:false, searchable:false, width:'100px'}, {data:'drop_time', orderable:false, searchable:false, width:'100px'}, {data:'vehicle_name', orderable:false, searchable:false}, {data:'pickup_point'}, {data:'monthly_fee', orderable:false, searchable:false, className:'text-end', width:'120px'}, {data:'status', orderable:false, searchable:false, width:'90px'}, {data:'actions', orderable:false, searchable:false, width:'120px'}
                ]})
            };
            initTabPersistence('#transportTabs');

            const config = {
                vehicle: {modal: '#vehicleOffcanvas', store: '{{ route('admin.transport.vehicles.store') }}', table: tables.vehicles},
                driver: {modal: '#driverOffcanvas', store: '{{ route('admin.transport.drivers.store') }}', table: tables.drivers},
                route: {modal: '#routeOffcanvas', store: '{{ route('admin.transport.routes.store') }}', table: tables.routes},
                'route-stop': {modal: '#routeStopOffcanvas', store: '{{ route('admin.transport.route-stops.store') }}', table: tables.routeStops},
                assignment: {modal: '#assignmentOffcanvas', store: '{{ route('admin.transport.assignments.store') }}', table: tables.assignments}
            };

            function toggleDriverLoginFields(existingUser) {
                const checked = !!$('#driverOffcanvas [name="enable_login"]').prop('checked');
                $('#driverLoginFields').toggle(checked);
                $('#driverEmailLabel').toggleClass('required', checked);
                if (!checked) {
                    return;
                }
                // New login (create OR driver with no account yet) -> password required.
                // Existing login -> password optional (leave blank keeps current).
                const passwordRequired = !existingUser;
                $('#driverPasswordLabel, #driverPasswordConfirmLabel').toggleClass('required', passwordRequired);
                $('#driverPasswordHint').toggle(!passwordRequired);
            }

            $('#driverOffcanvas').on('change', '[name="enable_login"]', function () {
                const existingUser = $(this).data('existing-user');
                toggleDriverLoginFields(existingUser);
            });

            $(document).on('click', '.reset-driver-password', function () {
                $('#driverResetPasswordForm')[0].reset();
                $('#driverResetPasswordForm').attr('action', $(this).data('url'));
                $('#driverResetPasswordForm').find('.is-invalid').removeClass('is-invalid');
                $('#driverResetPasswordForm').find('.invalid-feedback.dynamic').remove();
                $('#driverResetPasswordName').text($(this).data('name'));
                $('#driverResetPasswordForm').data('is-dirty', false);
                bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('driverResetPasswordOffcanvas')).show();
            });

            function populateAssignmentStops(routeId, selectedStopId) {
                const $stop = $('#assignmentStop');
                const stops = routeId ? ROUTE_STOPS.filter(s => s.route_id == routeId) : [];
                const options = stops.map(s => ({
                    id: s.id,
                    text: s.stop_name + (s.pickup_time ? ' (' + s.pickup_time + ')' : '')
                }));
                const label = routeId ? (stops.length ? 'Select Stop' : 'No stops available') : 'Select Route First';
                App.refreshSelect2Options('#assignmentStop', options, true);
                $stop.find('option:first').text(label);
                if (selectedStopId && stops.some(s => s.id == selectedStopId)) {
                    $stop.val(selectedStopId).trigger('change');
                }
                if (!selectedStopId) {
                    $('#assignmentPickupTime').val('');
                    $('#assignmentDropTime').val('');
                }
                $stop.prop('disabled', !routeId);
            }

            function setAssignmentTimes(stopId) {
                if (!stopId) {
                    $('#assignmentPickupTime').val('');
                    $('#assignmentDropTime').val('');
                    return;
                }
                const stop = ROUTE_STOPS.find(s => s.id == stopId);
                if (stop) {
                    $('#assignmentPickupTime').val(stop.pickup_time || '');
                    $('#assignmentDropTime').val(stop.drop_time || '');
                }
            }

            function setAssignmentVehicle(routeId) {
                const route = ROUTES.find(r => r.id == routeId);
                if (route && route.vehicle_id) {
                    $('#assignmentVehicle').val(route.vehicle_id);
                }
            }

            $('.open-modal').on('click', function () {
                const modalId = $(this).data('modal');
                const form = $(`${modalId} form`);
                const setup = Object.values(config).find(item => item.modal === modalId);
                form[0].reset();
                form.attr('action', setup.store);
                form.find('[name="_method"]').val('POST');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
                if (modalId === '#assignmentOffcanvas') {
                    form.find('[name="student_id"]').val('').trigger('change');
                    populateAssignmentStops('', null);
                    $('#assignmentVehicle').val('');
                    $('#assignmentPickupTime').val('');
                    $('#assignmentDropTime').val('');
                }
                if (modalId === '#driverOffcanvas') {
                    const $checkbox = form.find('[name="enable_login"]');
                    $checkbox.data('existing-user', false);
                    toggleDriverLoginFields(false);
                }
                form.find('select').trigger('change.select2');
                form.data('is-dirty', false);
                bootstrap.Offcanvas.getOrCreateInstance(document.querySelector(modalId)).show();
            });

            $(document).on('change', '#assignmentRoute', function () {
                const routeId = $(this).val();
                populateAssignmentStops(routeId, null);
                setAssignmentVehicle(routeId);
                $('#assignmentPickupTime').val('');
                $('#assignmentDropTime').val('');
            });

            $(document).on('change', '#assignmentStop', function () {
                setAssignmentTimes($(this).val());
            });

            $('.transport-form').on('erp:success', function () {
                const offcanvasEl = $(this).closest('.offcanvas')[0];
                if (offcanvasEl) {
                    bootstrap.Offcanvas.getInstance(offcanvasEl).hide();
                } else {
                    const modalEl = $(this).closest('.modal')[0];
                    if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
                }
                Object.values(tables).forEach(table => table.ajax.reload(null, false));
            });

            $(document).on('click', '.edit-transport', function () {
                const type = $(this).data('type');
                const setup = config[type];
                const form = $(`${setup.modal} form`);
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
                    if (type === 'assignment') {
                        const routeId = response.data.route_id;
                        const stopId = response.data.route_stop_id;
                        // Fix AJAX student Select2 — create option from loaded relation
                        const student = response.data.student;
                        if (student) {
                            const $st = form.find('[name="student_id"]');
                            const name = [student.first_name, student.middle_name, student.last_name].filter(Boolean).join(' ');
                            const optText = name + ' (' + student.admission_no + ')';
                            if (!$st.find('option[value="' + student.id + '"]').length) {
                                $st.append(new Option(optText, student.id, true, true));
                            }
                            $st.val(student.id).trigger('change');
                        }
                        // Update route Select2 display
                        $('#assignmentRoute').val(routeId).trigger('change');
                        // change handler already called populateAssignmentStops(null) + setAssignmentVehicle;
                        // re-call with correct stopId to select the saved stop
                        populateAssignmentStops(routeId, stopId);
                        setAssignmentTimes(stopId);
                    }
                    if (type === 'driver') {
                        const existingUser = !!response.data.user?.id;
                        const $checkbox = form.find('[name="enable_login"]');
                        $checkbox.data('existing-user', existingUser);
                        form.find('[name="password"]').val('');
                        form.find('[name="password_confirmation"]').val('');
                        form.find('#driverPasswordHint').text(existingUser ? 'New password. Leave blank to keep the current password.' : '');
                        toggleDriverLoginFields(existingUser);
                    }
                    form.find('select').trigger('change.select2');
                    form.data('is-dirty', false);
                    bootstrap.Offcanvas.getOrCreateInstance(document.querySelector(setup.modal)).show();
                });
            });

            $(document).on('click', '.delete-transport', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => Object.values(tables).forEach(table => table.ajax.reload(null, false))
                });
            });

            $(document).on('click', '.view-route', function () {
                const url = $(this).data('url');
                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('routeDetailModal'));
                $('#routeDetailContent').html('<div class="col-12 text-center py-5"><div class="spinner-border" role="status"></div><p class="mt-2 text-secondary">Loading route details...</p></div>');
                modal.show();
                $.get(url, (response) => {
                    if (!response.success) {
                        $('#routeDetailContent').html('<div class="col-12"><div class="alert alert-danger mb-0">Failed to load route details.</div></div>');
                        return;
                    }
                    const r = response.data;
                    const route = r.route;
                    const stopsCount = route.stops?.length ?? 0;
                    let html = `
                        <div class="col-12">
                            <div class="card card-sm mb-0">
                                <div class="card-body">
                                    <div class="row g-2">
                                        <div class="col-md-3"><strong>Route:</strong> ${route.route_name ?? '-'}</div>
                                        <div class="col-md-3"><strong>Start:</strong> ${route.start_point ?? '-'}</div>
                                        <div class="col-md-3"><strong>End:</strong> ${route.end_point ?? '-'}</div>
                                        <div class="col-md-3"><strong>Distance:</strong> ${route.distance ?? '-'} km</div>
                                        <div class="col-md-3"><strong>Vehicle:</strong> ${route.vehicle?.vehicle_number ?? '-'}</div>
                                        <div class="col-md-3"><strong>Driver:</strong> ${route.driver?.name ?? '-'}</div>
                                        <div class="col-md-3"><strong>Status:</strong> <span class="badge bg-${route.status === 'active' ? 'success-subtle text-success' : 'secondary-subtle text-secondary'}">${route.status}</span></div>
                                        <div class="col-md-3"><strong>Total Stops:</strong> ${stopsCount}</div>
                                    </div>
                                </div>
                            </div>
                        </div>`;

                    const renderFlow = (stops, label, arrowIcon) => {
                        if (!stops || !stops.length) return '<p class="text-secondary">No stops.</p>';
                        return stops.map((s, i) => `
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill flex-shrink-0" style="width:24px">${i + 1}</span>
                                <span class="fw-medium">${s.stop_name}</span>
                                ${s.pickup_time ? `<span class="badge bg-info-subtle text-info">Pickup ${s.pickup_time}</span>` : ''}
                                ${s.drop_time ? `<span class="badge bg-warning-subtle text-warning">Drop ${s.drop_time}</span>` : ''}
                            </div>
                            ${i < stops.length - 1 ? `<div class="ms-3 mb-1 text-secondary"><i class="ti ${arrowIcon}"></i></div>` : ''}
                        `).join('');
                    };

                    html += `
                        <div class="col-md-6">
                            <div class="card card-sm mb-0 h-100">
                                <div class="card-header"><h6 class="mb-0"><i class="ti ti-arrow-up-circle text-success me-1"></i>Pickup Order</h6></div>
                                <div class="card-body">${renderFlow(r.pickup_order, 'Stops in pickup sequence', 'ti-arrow-down')}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-sm mb-0 h-100">
                                <div class="card-header"><h6 class="mb-0"><i class="ti ti-arrow-down-circle text-danger me-1"></i>Drop Order</h6></div>
                                <div class="card-body">${renderFlow(r.drop_order, 'Stops in reverse sequence', 'ti-arrow-down')}</div>
                            </div>
                        </div>`;

                    $('#routeDetailContent').html(html);
                }).fail(() => {
                    $('#routeDetailContent').html('<div class="col-12"><div class="alert alert-danger mb-0">Failed to load route details. Please try again.</div></div>');
                });
            });
        })(); });
    </script>
@endpush
