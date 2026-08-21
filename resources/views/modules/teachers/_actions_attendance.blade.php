<x-erp.table-action-menu>
    @can('teachers.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-attendance" data-url="{{ route('admin.teachers.attendance.show', $attendance) }}" data-update-url="{{ route('admin.teachers.attendance.update', $attendance) }}">
                <i class="ti ti-pencil me-2"></i> Edit
            </button>
        </li>
    @endcan
    @can('teachers.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-attendance" data-url="{{ route('admin.teachers.attendance.destroy', $attendance) }}">
                <i class="ti ti-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
