<x-erp.table-action-menu>
    @can('academic_calendar.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-event"
                    data-id="{{ $event->id }}" title="Edit Event"><i class="ti ti-pencil me-2"></i> Edit</button>
        </li>
    @endcan
    @can('academic_calendar.publish')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center toggle-publish"
                    data-id="{{ $event->id }}" title="{{ $event->is_published ? 'Unpublish' : 'Publish' }}">
                <i class="ti ti-{{ $event->is_published ? 'eye-off' : 'eye' }} me-2"></i> {{ $event->is_published ? 'Unpublish' : 'Publish' }}
            </button>
        </li>
    @endcan
    @can('academic_calendar.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-event"
                    data-id="{{ $event->id }}" title="Delete Event"><i class="ti ti-trash me-2"></i> Delete</button>
        </li>
    @endcan
</x-erp.table-action-menu>
