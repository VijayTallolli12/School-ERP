@can('transport.update')
    <button type="button" class="btn btn-sm btn-light border handle-sos"
            data-url="{{ route('admin.transport.sos.show', $sos) }}"
            data-update-url="{{ route('admin.transport.sos.update', $sos) }}">
        <i class="ti ti-alert-octagon me-1"></i>Take Action
    </button>
@else
    <span class="text-muted small">—</span>
@endcan