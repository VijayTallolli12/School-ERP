{{--
    ERP Empty State (Gold Standard)
    Props:
        icon    (string) Tabler icon name WITHOUT the `ti ti-` prefix
        title   (string) Bold title text
        message (string) Subtitle/description text
        action  (array)  Optional action link { label, route }
--}}
<div class="text-center py-5 px-3">
    <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; border-radius: 50%; background-color: var(--erp-primary-light, #eef2ff); color: var(--erp-primary, #4f46e5);">
        <i class="ti ti-{{ $icon ?? 'inbox' }}" style="font-size: 2rem;"></i>
    </div>
    <h4 class="fw-bold text-dark mb-2" style="font-size: 1.125rem;">{{ $title ?? 'No Data Available' }}</h4>
    <p class="text-secondary mb-4 mx-auto" style="max-width: 300px; font-size: 0.875rem;">
        {{ $message ?? 'There is currently no data to display in this section.' }}
    </p>
    @if(isset($action) && isset($action['route']) && isset($action['label']))
        <a href="{{ $action['route'] }}" class="text-decoration-none fw-semibold" style="color: var(--erp-primary, #4f46e5); font-size: 0.875rem;">
            {{ $action['label'] }}
        </a>
    @endif
</div>
