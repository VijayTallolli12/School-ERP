<div class="d-flex flex-nowrap gap-1">
    @can('notifications.update')
        <button type="button" class="btn btn-sm btn-outline-secondary edit-notification"
                data-url="{{ route('admin.notifications.show', $notification) }}"
                data-update-url="{{ route('admin.notifications.update', $notification) }}"
                title="Edit">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @if($notification->status === 'draft' && auth()->user()->can('notifications.send'))
        <button type="button" class="btn btn-sm btn-outline-success send-notification"
                data-url="{{ route('admin.notifications.send', $notification) }}"
                title="Send">
            <i class="ti ti-send"></i>
        </button>
    @endif
    @can('notifications.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-notification"
                data-url="{{ route('admin.notifications.destroy', $notification) }}"
                title="Delete">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>