<div class="btn-group dropdown" data-debug-slot-id="{{ $timetableSlot->id }}">
    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        Actions
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @can('timetable.update')
            <li>
                <button type="button" class="dropdown-item edit-slot"
                    data-id="{{ $timetableSlot->id }}"
                    data-url="{{ route('admin.timetable.show', $timetableSlot) }}"
                    data-update-url="{{ route('admin.timetable.update', $timetableSlot) }}">
                    <i class="ti ti-pencil me-2"></i> Edit
                </button>
            </li>
        @endcan
        @can('timetable.delete')
            <li>
                <button type="button" class="dropdown-item text-danger delete-slot"
                    data-id="{{ $timetableSlot->id }}"
                    data-url="{{ route('admin.timetable.destroy', $timetableSlot) }}">
                    <i class="ti ti-trash me-2"></i> Delete
                </button>
            </li>
        @endcan
    </ul>
</div>
