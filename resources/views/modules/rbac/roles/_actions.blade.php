<x-erp.table-action-menu>
    @can('roles.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-role"
                    data-url="{{ route('admin.roles.show', $role) }}"
                    data-update-url="{{ route('admin.roles.update', $role) }}">
                <i class="ti ti-pencil me-2"></i> Edit
            </button>
        </li>
    @endcan
    @can('roles.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-role" data-url="{{ route('admin.roles.destroy', $role) }}">
                <i class="ti ti-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
