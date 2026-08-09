@extends('layouts.admin')

@section('title', 'SOS Alerts')
@section('page-title', 'SOS Alerts')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.transport.index') }}">Transport</a></li>
    <li class="breadcrumb-item active">SOS Alerts</li>
@endsection

@section('content')
    <div class="row g-3 mb-4">
        <x-erp.stat-card label="Open (New)" :value="$stats['new']" icon="alert-octagon" color="danger" />
        <x-erp.stat-card label="Acknowledged" :value="$stats['acknowledged']" icon="eye" color="warning" />
        <x-erp.stat-card label="Resolved" :value="$stats['resolved']" icon="check" color="success" />
        <x-erp.stat-card label="Total" :value="$stats['total']" icon="list" color="secondary" />
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ti ti-alert-octagon text-danger me-2"></i>Driver SOS Alerts</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered w-100" id="sosTable">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>Driver</th>
                        <th>Trip</th>
                        <th>Vehicle</th>
                        <th>Message</th>
                        <th>Location</th>
                        <th width="120">Status</th>
                        <th width="150">Triggered</th>
                        <th width="130">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <div class="modal fade" id="sosActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">SOS Alert #<span id="sosId">-</span></h5>
                    <button class="btn-close" data-bs-dismiss="modal" type="button"></button>
                </div>
                <div class="modal-body" id="sosModalBody">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="card card-sm mb-2"><div class="card-body py-2">
                                <div class="text-muted small">Driver</div>
                                <div class="fw-semibold" id="sosDriver">-</div>
                            </div></div>
                            <div class="card card-sm mb-2"><div class="card-body py-2">
                                <div class="text-muted small">Mobile</div>
                                <div class="fw-semibold" id="sosMobile">-</div>
                            </div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-sm mb-2"><div class="card-body py-2">
                                <div class="text-muted small">Trip / Route</div>
                                <div class="fw-semibold"><span id="sosTrip">-</span> · <span id="sosRoute">-</span></div>
                            </div></div>
                            <div class="card card-sm mb-2"><div class="card-body py-2">
                                <div class="text-muted small">Vehicle</div>
                                <div class="fw-semibold" id="sosVehicle">-</div>
                            </div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-sm mb-2"><div class="card-body py-2">
                                <div class="text-muted small">Triggered At</div>
                                <div class="fw-semibold" id="sosTriggered">-</div>
                            </div></div>
                            <a id="sosMapLink" href="#" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary d-none"><i class="ti ti-map-pin me-1"></i>View on map</a>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-sm mb-2"><div class="card-body py-2">
                                <div class="text-muted small">Handled By</div>
                                <div class="fw-semibold" id="sosHandledInfo">-</div>
                            </div></div>
                        </div>
                        <div class="col-12">
                            <div class="card card-sm"><div class="card-body py-2">
                                <div class="text-muted small">Message</div>
                                <div id="sosMessage" class="fw-medium">-</div>
                            </div></div>
                        </div>
                    </div>

                    <form class="ajax-form" id="sosActionForm" method="POST">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label required">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="new">New</option>
                                    <option value="acknowledged">Acknowledged</option>
                                    <option value="resolved">Resolved</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Action / Notes</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="e.g. Contacted driver, ambulance dispatched, resolved."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer px-0 pb-0">
                            <button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button>
                            <button class="btn btn-primary py-2" type="submit"><i class="ti ti-device-floppy me-1"></i>Save Action</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
<script>
    $(function () {
        const table = $('#sosTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('admin.transport.sos.data') }}',
            responsive: true,
            autoWidth: false,
            order: [[0, 'desc']],
            columns: [
                { data: 'id', name: 'id' },
                { data: 'driver_info', orderable: false },
                { data: 'trip_info', orderable: false },
                { data: 'vehicle_number', orderable: false },
                { data: 'message', orderable: false },
                { data: 'location', orderable: false },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', orderable: false, searchable: false },
            ],
            initComplete: function () {
                if (window.console) {
                    console.log('[SOS DT] initialized');
                }
            },
        });

        $(document).on('click', '.handle-sos', function () {
            const $btn = $(this);
            const form = $('#sosActionForm');
            form[0].reset();
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback.dynamic').remove();

            $('#sosModalBody').find('.card, #sosMapLink, form').addClass('d-none');
            $('#sosModalBody').prepend('<div class="text-center py-5" id="sosLoader"><div class="spinner-border" role="status"></div><p class="mt-2 text-secondary">Loading SOS details...</p></div>');

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('sosActionModal'));
            modal.show();

            $.get($btn.data('url'), (res) => {
                if (!res.success) {
                    $('#sosLoader').replaceWith('<div class="alert alert-danger mb-0">Failed to load SOS details.</div>');
                    return;
                }
                const d = res.data;
                $('#sosLoader').remove();
                $('#sosId').text(d.id);
                $('#sosDriver').text(d.driver || '-');
                $('#sosMobile').text(d.driver_mobile || '-');
                $('#sosTrip').text(d.trip_id ? 'Trip #' + d.trip_id : '-');
                $('#sosRoute').text(d.route_name || '-');
                $('#sosVehicle').text(d.vehicle_number || '-');
                $('#sosTriggered').text(d.triggered_at || '-');
                $('#sosHandledInfo').text(d.handled_at ? (d.handled_by + ' · ' + d.handled_at) : '-');
                $('#sosMessage').text(d.message || 'No message.');
                const lat = d.latitude;
                const lng = d.longitude;
                const mapLink = $('#sosMapLink');
                if (lat != null && lng != null) {
                    mapLink.attr('href', 'https://www.google.com/maps?q=' + lat + ',' + lng).removeClass('d-none');
                } else {
                    mapLink.addClass('d-none');
                }
                form.attr('action', $btn.data('update-url'));
                $('#sosModalBody').find('.card, form, #sosMapLink').removeClass('d-none');
                form.find('[name="status"]').val(d.status || 'new');
                form.find('[name="notes"]').val(d.notes || '');
            }).fail(() => {
                $('#sosLoader').replaceWith('<div class="alert alert-danger mb-0">Failed to load SOS details.</div>');
            });
        });

        $('#sosActionForm').on('erp:success', function () {
            bootstrap.Modal.getInstance(document.getElementById('sosActionModal')).hide();
            // Reload page so the stat cards reflect the new status.
            window.location.reload();
        });
    });
</script>
@endpush