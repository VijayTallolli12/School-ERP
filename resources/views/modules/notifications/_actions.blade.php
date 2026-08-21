<x-erp.table-action-menu>
    @can('notifications.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-notification"
                    data-url="{{ route('admin.notifications.show', $notification) }}"
                    data-update-url="{{ route('admin.notifications.update', $notification) }}"
                    title="Edit">
                <i class="ti ti-pencil me-2"></i> Edit
            </button>
        </li>
    @endcan
    @if($notification->status === 'draft' && auth()->user()->can('notifications.send'))
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center send-notification"
                    data-url="{{ route('admin.notifications.send', $notification) }}"
                    title="Send">
                <i class="ti ti-send me-2"></i> Send
            </button>
        </li>
    @endif
    @can('notifications.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-notification"
                    data-url="{{ route('admin.notifications.destroy', $notification) }}"
                    title="Delete">
                <i class="ti ti-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>