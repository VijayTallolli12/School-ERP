<div class="btn-group dropdown">
    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        Actions
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @can('timetable.update')
            <li>
                <button type="button" class="dropdown-item edit-slot" data-url="{{ route('admin.timetable.show', $slot) }}" data-update-url="{{ route('admin.timetable.update', $slot) }}">
                    <i class="ti ti-pencil me-2"></i> Edit
                </button>
            </li>
        @endcan
        @can('timetable.delete')
            <li>
                <button type="button" class="dropdown-item text-danger delete-slot" data-url="{{ route('admin.timetable.destroy', $slot) }}">
                    <i class="ti ti-trash me-2"></i> Delete
                </button>
            </li>
        @endcan
    </ul>
</div>
