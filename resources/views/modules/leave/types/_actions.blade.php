<x-erp.table-action-menu>
@can('leave_management.update')
    <li>
        <button type="button" class="dropdown-item d-flex align-items-center edit-leave-type"
                data-url="{{ route('admin.leave-types.show', $type) }}"
                data-update-url="{{ route('admin.leave-types.update', $type) }}"
                title="Edit">
            <i class="ti ti-pencil me-2"></i> Edit
        </button>
    </li>
@endcan
@can('leave_management.delete')
    <li>
        <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-leave-type"
                data-url="{{ route('admin.leave-types.destroy', $type) }}"
                title="Delete">
            <i class="ti ti-trash me-2 text-danger"></i> Delete
        </button>
    </li>
@endcan
</x-erp.table-action-menu>
