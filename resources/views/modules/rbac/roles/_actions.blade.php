<div class="table-actions">
    @can('roles.update')
        <button type="button" class="btn btn-sm btn-outline-primary edit-role"
                data-url="{{ route('admin.roles.show', $role) }}"
                data-update-url="{{ route('admin.roles.update', $role) }}">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @can('roles.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-role" data-url="{{ route('admin.roles.destroy', $role) }}">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
