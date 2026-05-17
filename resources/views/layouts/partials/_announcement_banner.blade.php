{{-- Announcement Banner Partial --}}
<div class="d-none" id="announcementBanner">
    <div class="alert m-0 rounded-0 border-0 d-flex align-items-center justify-content-between" id="announcementBannerContent">
        <div class="d-flex align-items-center gap-2">
            <i class="ti ti-megaphone"></i>
            <span id="announcementBannerText">--</span>
        </div>
        <button type="button" class="btn-close" id="announcementBannerClose" aria-label="Dismiss"></button>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const banner = document.getElementById('announcementBanner');
        const bannerText = document.getElementById('announcementBannerText');
        const bannerClose = document.getElementById('announcementBannerClose');
        const bannerContent = document.getElementById('announcementBannerContent');

        // Priority-based alert classes
        const priorityClasses = {
            urgent: 'alert-danger',
            high: 'alert-warning',
            low: 'alert-secondary',
            medium: 'alert-info',
        };

        fetch('{{ route('admin.notifications.bell') }}', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(res => {
                if (!res.success || !res.data) return;

                // Find the first unread announcement
                const announcements = (res.data.notifications || []).filter(n => n.type === 'announcement' && !n.is_read);
                if (announcements.length === 0) return;

                const latest = announcements[0];
                bannerText.textContent = latest.title + (latest.message ? ' — ' + latest.message.substring(0, 100) : '');
                bannerContent.className = 'alert m-0 rounded-0 border-0 d-flex align-items-center justify-content-between ' + (priorityClasses[latest.priority] || 'alert-info');
                banner.classList.remove('d-none');

                // Dismiss handler
                bannerClose.addEventListener('click', () => {
                    if (latest.id) {
                        fetch('{{ route('admin.notifications.markRead', '__ID__') }}'.replace('__ID__', latest.id), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            }
                        });
                    }
                    banner.classList.add('d-none');
                });
            });

        // Update bell badge is handled by _bell.blade.php independently
    })();
</script>
@endpush