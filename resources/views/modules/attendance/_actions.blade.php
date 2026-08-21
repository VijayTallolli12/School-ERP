<x-erp.table-action-menu>
    @can('attendance.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center" onclick="editAttendance({{ $attendance->id }})" title="Edit">
                <i class="ti ti-pencil me-2"></i> Edit
            </button>
        </li>
    @endcan
    @can('attendance.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-attendance" data-url="{{ route('admin.attendance.destroy', $attendance) }}" title="Delete">
                <i class="ti ti-trash me-2"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
