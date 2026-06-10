<div class="d-flex gap-1 justify-content-end">
    @can('academic_calendar.update')
        <button type="button" class="btn btn-sm btn-outline-primary edit-event"
                data-id="{{ $event->id }}" title="Edit Event">
            <i class="ti ti-edit"></i>
        </button>
    @endcan
    @can('academic_calendar.publish')
        <button type="button" class="btn btn-sm btn-outline-{{ $event->is_published ? 'secondary' : 'success' }} toggle-publish"
                data-id="{{ $event->id }}" title="{{ $event->is_published ? 'Unpublish' : 'Publish' }}">
            <i class="ti ti-{{ $event->is_published ? 'eye-off' : 'eye' }}"></i>
        </button>
    @endcan
    @can('academic_calendar.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-event"
                data-id="{{ $event->id }}" title="Delete Event">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
