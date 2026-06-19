@extends('layouts.admin')

@section('title', 'Transport Reports')
@section('page-title', 'Transport Reports')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.transport.index') }}">Transport</a></li>
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#vehicleReportPane" type="button"><i class="ti ti-bus me-1"></i>Vehicle Report</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#driverReportPane" type="button"><i class="ti ti-user me-1"></i>Driver Report</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#routeReportPane" type="button"><i class="ti ti-map me-1"></i>Route Report</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#routeStudentsPane" type="button"><i class="ti ti-users me-1"></i>Route-wise Students</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#occupancyPane" type="button"><i class="ti ti-chart-bar me-1"></i>Vehicle Occupancy</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#feeReportPane" type="button"><i class="ti ti-wallet me-1"></i>Transport Fee</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="vehicleReportPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="vFilterType"><option value="">All Types</option><option value="bus">Bus</option><option value="van">Van</option><option value="car">Car</option><option value="other">Other</option></select></div>
                        <div class="col-auto"><select class="form-select form-select-sm" id="vFilterStatus"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="vFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.transport.reports.export.excel', 'vehicles') }}" id="vExcel"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.transport.reports.export.pdf', 'vehicles') }}" id="vPdf"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.transport.reports.print', 'vehicles') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="vehicleReportTable">
                        <thead><tr><th>#</th><th>Number</th><th>Name</th><th>Type</th><th>Capacity</th><th>Driver</th><th>Attendant</th><th>Occupancy</th><th>Status</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="driverReportPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="dFilterStatus"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="dFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.transport.reports.export.excel', 'drivers') }}"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.transport.reports.export.pdf', 'drivers') }}"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.transport.reports.print', 'drivers') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="driverReportTable">
                        <thead><tr><th>#</th><th>Name</th><th>Mobile</th><th>License No</th><th>License Expiry</th><th>Assigned Routes</th><th>Vehicles</th><th>Status</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="routeReportPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="rFilterStatus"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="rFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.transport.reports.export.excel', 'routes') }}"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.transport.reports.export.pdf', 'routes') }}"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.transport.reports.print', 'routes') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="routeReportTable">
                        <thead><tr><th>#</th><th>Route Name</th><th>Start</th><th>End</th><th>Distance</th><th>Vehicle</th><th>Driver</th><th>Stops</th><th>Students</th><th>Status</th><th width="80">Flow</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="routeStudentsPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="rsFilterRoute"><option value="">All Routes</option>@foreach($routes as $route)<option value="{{ $route->id }}">{{ $route->route_name }}</option>@endforeach</select></div>
                        <div class="col-auto"><select class="form-select form-select-sm" id="rsFilterStatus"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="rsFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.transport.reports.export.excel', 'route_students') }}"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.transport.reports.export.pdf', 'route_students') }}"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.transport.reports.print', 'route_students') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="routeStudentsTable">
                        <thead><tr><th>#</th><th>Student</th><th>Route</th><th>Stop</th><th>Pickup Time</th><th>Drop Time</th><th>Vehicle</th><th>Monthly Fee</th><th>Status</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="occupancyPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="oFilterType"><option value="">All Types</option><option value="bus">Bus</option><option value="van">Van</option><option value="car">Car</option><option value="other">Other</option></select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="oFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.transport.reports.export.excel', 'vehicle_occupancy') }}"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.transport.reports.export.pdf', 'vehicle_occupancy') }}"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.transport.reports.print', 'vehicle_occupancy') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="occupancyTable">
                        <thead><tr><th>#</th><th>Vehicle Number</th><th>Name</th><th>Type</th><th>Capacity</th><th>Assigned</th><th>Available</th><th>Occupancy %</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="feeReportPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="fFilterStatus"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="fFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.transport.reports.export.excel', 'transport_fee') }}"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.transport.reports.export.pdf', 'transport_fee') }}"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.transport.reports.print', 'transport_fee') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="feeReportTable">
                        <thead><tr><th>#</th><th>Route</th><th>Students</th><th>Total Fee</th></tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <div class="modal fade" id="routeFlowModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Route Flow</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body">
                    <div id="routeFlowContent" class="row g-3"></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Close</button></div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();

            const baseExcel = '{{ route('admin.transport.reports.export.excel', 'REPLACE') }}';
            const basePdf = '{{ route('admin.transport.reports.export.pdf', 'REPLACE') }}';
            const basePrint = '{{ route('admin.transport.reports.print', 'REPLACE') }}';

            function updateExportLinks(prefix, reportKey, params) {
                const qs = $.param(params);
                $(`#${prefix}Excel`).attr('href', baseExcel.replace('REPLACE', reportKey) + '?' + qs);
                $(`#${prefix}Pdf`).attr('href', basePdf.replace('REPLACE', reportKey) + '?' + qs);
            }

            const vTable = $('#vehicleReportTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.transport.reports.vehicles.data') }}', data: d => { d.vehicle_type = $('#vFilterType').val(); d.status = $('#vFilterStatus').val(); }}, columns: [
                {data:'id'}, {data:'vehicle_number'}, {data:'vehicle_name'}, {data:'vehicle_type'}, {data:'capacity'}, {data:'driver_name', orderable:false}, {data:'attendant'}, {data:'occupancy', orderable:false, searchable:false}, {data:'status'}
            ]});
            $('#vFilterBtn').on('click', () => { vTable.ajax.reload(); updateExportLinks('v', 'vehicles', {vehicle_type: $('#vFilterType').val(), status: $('#vFilterStatus').val()}); });

            const dTable = $('#driverReportTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.transport.reports.drivers.data') }}', data: d => { d.status = $('#dFilterStatus').val(); }}, columns: [
                {data:'id'}, {data:'name'}, {data:'mobile'}, {data:'license_number'}, {data:'license_expiry_date'}, {data:'assigned_routes', searchable:false}, {data:'vehicles_count', searchable:false}, {data:'status'}
            ]});
            $('#dFilterBtn').on('click', () => { dTable.ajax.reload(); });

            const rTable = $('#routeReportTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.transport.reports.routes.data') }}', data: d => { d.status = $('#rFilterStatus').val(); }}, columns: [
                {data:'id'}, {data:'route_name'}, {data:'start_point'}, {data:'end_point'}, {data:'distance'}, {data:'vehicle_name', orderable:false}, {data:'driver_name', orderable:false}, {data:'stops_count', searchable:false}, {data:'assignments_count', searchable:false}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
            ]});
            $('#rFilterBtn').on('click', () => { rTable.ajax.reload(); });

            const rsTable = $('#routeStudentsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.transport.reports.route-students.data') }}', data: d => { d.route_id = $('#rsFilterRoute').val(); d.status = $('#rsFilterStatus').val(); }}, columns: [
                {data:'id'}, {data:'student_name', orderable:false}, {data:'route_name', orderable:false}, {data:'stop_name', orderable:false}, {data:'pickup_time', orderable:false, searchable:false}, {data:'drop_time', orderable:false, searchable:false}, {data:'vehicle_name', orderable:false}, {data:'monthly_fee'}, {data:'status'}
            ]});
            $('#rsFilterBtn').on('click', () => { rsTable.ajax.reload(); });

            const oTable = $('#occupancyTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.transport.reports.vehicle-occupancy.data') }}', data: d => { d.vehicle_type = $('#oFilterType').val(); }}, columns: [
                {data:'id'}, {data:'vehicle_number'}, {data:'vehicle_name'}, {data:'vehicle_type'}, {data:'capacity_display'}, {data:'occupancy_count', searchable:false}, {data:'available', searchable:false, orderable:false}, {data:'occupancy_pct', searchable:false, orderable:false}
            ]});
            $('#oFilterBtn').on('click', () => { oTable.ajax.reload(); });

            const fTable = $('#feeReportTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.transport.reports.fee.data') }}', data: d => { d.status = $('#fFilterStatus').val(); }}, columns: [
                {data:'route_id'}, {data:'route_name', orderable:false}, {data:'student_count', searchable:false}, {data:'total_fee', searchable:false}
            ]});
            $('#fFilterBtn').on('click', () => { fTable.ajax.reload(); });
            initTabPersistence('#reportTabs');

            $(document).on('click', '.view-route-flow', function () {
                const url = $(this).data('url');
                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('routeFlowModal'));
                $('#routeFlowContent').html('<div class="col-12 text-center py-5"><div class="spinner-border" role="status"></div><p class="mt-2 text-secondary">Loading route flow...</p></div>');
                modal.show();
                $.get(url, (response) => {
                    if (!response.success) {
                        $('#routeFlowContent').html('<div class="col-12"><div class="alert alert-danger mb-0">Failed to load route details.</div></div>');
                        return;
                    }
                    const r = response.data;
                    const route = r.route;
                    let html = `
                        <div class="col-12">
                            <div class="card card-sm mb-0">
                                <div class="card-body">
                                    <div class="row g-2">
                                        <div class="col-md-4"><strong>Route:</strong> ${route.route_name ?? '-'}</div>
                                        <div class="col-md-4"><strong>Vehicle:</strong> ${route.vehicle?.vehicle_number ?? '-'}</div>
                                        <div class="col-md-4"><strong>Driver:</strong> ${route.driver?.name ?? '-'}</div>
                                        <div class="col-md-4"><strong>Start:</strong> ${route.start_point ?? '-'}</div>
                                        <div class="col-md-4"><strong>End:</strong> ${route.end_point ?? '-'}</div>
                                        <div class="col-md-4"><strong>Status:</strong> <span class="badge bg-${route.status === 'active' ? 'success' : 'secondary'}">${route.status}</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>`;

                    const renderFlow = (stops, label, arrowIcon, headingIcon, headingColor) => {
                        if (!stops || !stops.length) return '<p class="text-secondary">No stops.</p>';
                        return stops.map((s, i) => `
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-secondary rounded-pill flex-shrink-0" style="width:24px">${i + 1}</span>
                                <span class="fw-medium">${s.stop_name}</span>
                                ${s.pickup_time ? `<span class="badge bg-info">Pickup ${s.pickup_time}</span>` : ''}
                                ${s.drop_time ? `<span class="badge bg-warning">Drop ${s.drop_time}</span>` : ''}
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

                    $('#routeFlowContent').html(html);
                }).fail(() => {
                    $('#routeFlowContent').html('<div class="col-12"><div class="alert alert-danger mb-0">Failed to load route details. Please try again.</div></div>');
                });
            });
        })(); });
    </script>
@endpush
