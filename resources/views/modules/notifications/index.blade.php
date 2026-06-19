@extends('layouts.admin')

@section('title', 'Notifications')
@section('page-title', 'Notification Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="notificationsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#listPane" type="button">
                        <i class="ti ti-list me-1"></i> All Notifications
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#dashboardPane" type="button">
                        <i class="ti ti-chart-pie me-1"></i> Dashboard
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                {{-- List pane --}}
                <div class="tab-pane fade show active" id="listPane">
                    <div class="d-flex flex-wrap gap-2 mb-3 justify-content-between align-items-center">
                        <div class="d-flex gap-2 flex-wrap">
                            <select class="form-select form-select-sm w-auto" id="filterType">
                                <option value="">All Types</option>
                                @foreach ($types as $k => $label)
                                    <option value="{{ $k }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <select class="form-select form-select-sm w-auto" id="filterStatus">
                                <option value="">All Statuses</option>
                                @foreach ($statuses as $s)
                                    <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @can('notifications.create')
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#notificationModal" id="createNotification">
                                <i class="ti ti-plus me-1"></i> New Notification
                            </button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="notificationsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Target</th>
                                <th>Channel</th>
                                <th>Recipients</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                {{-- Dashboard pane --}}
                <div class="tab-pane fade" id="dashboardPane">
                    <div class="row g-3" id="statsRow">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0 text-white" id="statTotalSent">--</h3>
                                    <small><i class="ti ti-check-circle me-1"></i>Total Sent</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center">
                                    <h3 class="mb-0 text-white" id="statPending">--</h3>
                                    <small><i class="ti ti-clock me-1"></i>Pending (Draft)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0 text-white" id="statFailed">--</h3>
                                    <small><i class="ti ti-alert-triangle me-1"></i>Failed</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0 text-white" id="statUnread">--</h3>
                                    <small><i class="ti ti-mail me-1"></i>Unread</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="notificationForm" method="POST" action="{{ route('admin.notifications.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="notificationMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalTitle">New Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Title</label>
                        <input class="form-control" name="title" required maxlength="200">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Type</label>
                        <select class="form-select" name="type" required>
                            <option value="">Select</option>
                            @foreach ($types as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Priority</label>
                        <select class="form-select" name="priority" required>
                            @foreach ($priorities as $p)
                                <option value="{{ $p }}" @if($p === 'medium') selected @endif>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label required">Message</label>
                        <textarea class="form-control" name="message" rows="4" required></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Target</label>
                        <select class="form-select" name="target_type" required>
                            @foreach ($targetTypes as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Channel</label>
                        <select class="form-select" name="channel" required>
                            @foreach ($channels as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="draft">Draft</option>
                            <option value="sent">Send Now</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Schedule Date</label>
                        <input type="datetime-local" class="form-control" name="scheduled_at">
                        <small class="text-secondary">Leave empty to send immediately when status is "Send Now".</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ti ti-x me-1"></i>Cancel</button>
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-device-floppy me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => { (async () => { const DataTable = await window.lazyDT();
            const notificationModal = new bootstrap.Modal('#notificationModal');
            const table = $('#notificationsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true, stateSave: true,
                ajax: {
                    url: '{{ route('admin.notifications.data') }}',
                    timeout: 15000,
                    data: function (d) {
                        d.type = $('#filterType').val();
                        d.status = $('#filterStatus').val();
                    },
                    error: function(xhr, error, thrown) {
                        console.error('[Notif DT] AJAX error:', error, thrown, xhr.responseJSON);
                        try { $('#notificationsTable').DataTable().processing(false); } catch (_) {}
                    }
                },
                columns: [
                    { data: 'id' },
                    { data: 'title' },
                    { data: 'type_label', orderable: false, searchable: false },
                    { data: 'priority_badge', orderable: false, searchable: false },
                    { data: 'target_label', orderable: false, searchable: false },
                    { data: 'channel_label', orderable: false, searchable: false },
                    { data: 'user_count', orderable: false, searchable: false },
                    { data: 'status_badge', orderable: false, searchable: false },
                    { data: 'created_by_name', orderable: false, searchable: false },
                    { data: 'actions', orderable: false, searchable: false }
                ],
                initComplete: function(settings, json) {
                    console.log('[Notif DT] init:', this.api().data().length, 'rows, total:', json?.recordsTotal);
                }
            });

            $('#filterType, #filterStatus').on('change', () => table.ajax.reload());

            // Create
            $('#createNotification').on('click', () => {
                const form = $('#notificationForm');
                form[0].reset();
                $('#notificationMethod').val('POST');
                form.attr('action', '{{ route('admin.notifications.store') }}');
                $('#notificationModalTitle').text('New Notification');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
            });

            // Edit
            $('#notificationsTable').on('click', '.edit-notification', function () {
                const form = $('#notificationForm');
                $.get($(this).data('url'), (res) => {
                    form[0].reset();
                    form.attr('action', $(this).data('update-url'));
                    $('#notificationMethod').val('PUT');
                    $('#notificationModalTitle').text('Edit Notification');
                    Object.entries(res.data).forEach(([k, v]) => {
                        if (v !== null && v !== undefined) form.find(`[name="${k}"]`).val(v);
                    });
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    notificationModal.show();
                });
            });

            // Delete
            $('#notificationsTable').on('click', '.delete-notification', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false)
                });
            });

            // Send
            $('#notificationsTable').on('click', '.send-notification', async function () {
                const Swal = await window.lazySwal();
                Swal.fire({
                    title: 'Send notification?',
                    text: 'This will deliver the notification to all target recipients.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Send',
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: $(this).data('url'),
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                        success(res) {
                            App.toast('success', res.message || 'Sent.');
                            table.ajax.reload(null, false);
                        },
                        error(xhr) {
                            App.toast('error', xhr.responseJSON?.message || 'Send failed.');
                        }
                    });
                });
            });

            // Form success handler
            $('#notificationForm').on('erp:success', () => {
                try {
                    notificationModal.hide();
                } catch (e) {
                    console.error('[Notif] modal hide error:', e);
                }
                try {
                    table.ajax.reload(null, false);
                } catch (e) {
                    console.error('[Notif] table reload error:', e);
                }
            });

            // Dashboard stats when tab shown
            document.querySelector('[data-bs-target="#dashboardPane"]')?.addEventListener('shown.bs.tab', () => {
                $.get('{{ route('admin.notifications.stats') }}', (res) => {
                    if (res.data) {
                        $('#statTotalSent').text(res.data.total_sent ?? 0);
                        $('#statPending').text(res.data.pending ?? 0);
                        $('#statFailed').text(res.data.failed ?? 0);
                        $('#statUnread').text(res.data.unread_count ?? 0);
                    }
                });
            });
        })(); });
        initTabPersistence('#notificationsTabs');
    </script>
@endpush