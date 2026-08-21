<x-erp.table-action-menu>
    @can('permissions.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-permission"
                    data-url="{{ route('admin.permissions.show', $permission) }}"
                    data-update-url="{{ route('admin.permissions.update', $permission) }}">
                <i class="ti ti-pencil me-2"></i> Edit
            </button>
        </li>
    @endcan
    @can('permissions.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-permission" data-url="{{ route('admin.permissions.destroy', $permission) }}">
                <i class="ti ti-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
