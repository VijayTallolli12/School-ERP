<x-erp.table-action-menu data-debug-slot-id="{{ $timetableSlot->id }}">
    @can('timetable.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-slot"
                data-id="{{ $timetableSlot->id }}"
                data-url="{{ route('admin.timetable.show', $timetableSlot) }}"
                data-update-url="{{ route('admin.timetable.update', $timetableSlot) }}"><i class="ti ti-pencil me-2"></i> Edit</button>
        </li>
    @endcan
    @can('timetable.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-slot"
                data-id="{{ $timetableSlot->id }}"
                data-url="{{ route('admin.timetable.destroy', $timetableSlot) }}"><i class="ti ti-trash me-2 text-danger"></i> Delete</button>
        </li>
    @endcan
</x-erp.table-action-menu>
