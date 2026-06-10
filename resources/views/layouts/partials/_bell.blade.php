{{-- Notification Bell Partial --}}
<li class="nav-item dropdown" id="notificationBellContainer">
    <a class="nav-link position-relative" data-bs-toggle="dropdown" href="#" role="button" aria-label="Notifications">
        <i class="ti ti-bell"></i>
        <span class="position-absolute top-0 start-75 translate-middle badge rounded-pill bg-danger d-none" id="notificationBadge">
            0
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 300px; max-width: calc(100vw - 16px); max-height: 420px; overflow: hidden;">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <strong class="small">Notifications</strong>
            <button type="button" class="btn btn-link btn-sm text-decoration-none small" id="markAllReadBtn">
                Mark all read
            </button>
        </div>
        <div style="max-height: 340px; overflow-y: auto;" id="notificationBellList">
            <div class="text-center py-4 text-muted small">
                <div class="spinner-border spinner-border-sm" role="status"></div>
                <span class="ms-1">Loading...</span>
            </div>
        </div>
        <a href="{{ route('admin.notifications.index') }}" class="dropdown-item text-center small py-2 border-top text-primary">
            View all notifications
        </a>
    </div>
</li>

@push('scripts')
<script>
    (function () {
        const container = document.getElementById('notificationBellContainer');
        const badge = document.getElementById('notificationBadge');
        const listEl = document.getElementById('notificationBellList');
        const markAllBtn = document.getElementById('markAllReadBtn');
        const bellUrl = '{{ route('admin.notifications.bell') }}';
        const markAllUrl = '{{ route('admin.notifications.markAllRead') }}';

        const typeIcons = {
            announcement: 'megaphone',
            fee_reminder: 'cash',
            attendance_alert: 'user-check',
            exam_result_alert: 'file-text',
            timetable_update: 'calendar',
        };

        const typeColors = {
            announcement: '#0d6efd',
            fee_reminder: '#dc3545',
            attendance_alert: '#198754',
            exam_result_alert: '#6f42c1',
            timetable_update: '#fd7e14',
        };

        function loadBell() {
            fetch(bellUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !res.data) return;
                    const data = res.data;
                    const count = data.unread_count || 0;

                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.classList.remove('d-none');
                    } else {
                        badge.classList.add('d-none');
                    }

                    const notifs = data.notifications || [];
                    if (notifs.length === 0) {
                        listEl.innerHTML = '<div class="text-center py-4 text-muted small">No notifications</div>';
                        return;
                    }

                    listEl.innerHTML = notifs.map(n => {
                        const icon = typeIcons[n.type] || 'bell';
                        const color = typeColors[n.type] || '#6c757d';
                        const bgClass = n.is_read ? '' : 'bg-light';
                        return `
                            <a href="#" class="dropdown-item d-flex align-items-start gap-2 py-2 ${bgClass} notification-item"
                               data-id="${n.id}" data-mark-url="{{ route('admin.notifications.markRead', '__ID__') }}'.replace('__ID__', n.id)"
                               style="border-bottom: 1px solid #f0f0f0;">
                                <span class="mt-1" style="color:${color};"><i class="ti ti-${icon}"></i></span>
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold small text-truncate" style="max-width:200px;">${escapeHtml(n.title)}</span>
                                        <small class="text-muted text-nowrap ms-1">${n.sent_at || ''}</small>
                                    </div>
                                    <small class="text-muted d-block text-truncate">${escapeHtml(n.message)}</small>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:0.65rem;">${n.type_label || ''}</span>
                                    ${!n.is_read ? '<span class="badge bg-primary ms-1" style="font-size:0.65rem;">New</span>' : ''}
                                </div>
                            </a>`;
                    }).join('');

                    // Click handler for individual mark-read
                    listEl.querySelectorAll('.notification-item').forEach(el => {
                        el.addEventListener('click', function (e) {
                            e.preventDefault();
                            const url = this.dataset.markUrl;
                            fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                }
                            }).then(() => loadBell());
                        });
                    });
                });
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Mark all read
        markAllBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            fetch(markAllUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                }
            }).then(() => loadBell());
        });

        // Load bell on page ready
        loadBell();

        // Reload bell when dropdown opens
        container.querySelector('[data-bs-toggle="dropdown"]').addEventListener('click', () => loadBell());
    })();
</script>
@endpush