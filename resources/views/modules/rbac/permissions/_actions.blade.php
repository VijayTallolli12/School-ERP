<div class="table-actions">
    @can('permissions.update')
        <button type="button" class="btn btn-sm btn-outline-primary edit-permission"
                data-url="{{ route('admin.permissions.show', $permission) }}"
                data-update-url="{{ route('admin.permissions.update', $permission) }}">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @can('permissions.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-permission" data-url="{{ route('admin.permissions.destroy', $permission) }}">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
