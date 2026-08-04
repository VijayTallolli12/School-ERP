{{--
    ERP Empty State
    Props:
        icon   (string)  Tabler icon name WITHOUT prefix (default: inbox)
        title  (string)  Heading text (default: No data available)
        message(string)  Supporting text
        actionLabel (string) optional button label
        actionUrl   (string) optional button URL
--}}
<div class="erp-empty-state">
    <div class="empty-icon">
        <i class="ti ti-{{ $icon ?? 'inbox' }}"></i>
    </div>
    <h5>{{ $title ?? 'No data available' }}</h5>
    @if($message ?? null)
        <p>{{ $message }}</p>
    @endif
    @if(($actionLabel ?? null) && ($actionUrl ?? null))
        <div class="empty-action">
            <a href="{{ $actionUrl }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>{{ $actionLabel }}
            </a>
        </div>
    @endif
</div>
