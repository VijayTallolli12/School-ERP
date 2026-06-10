@can('leave_management.update')
    <button type="button" class="btn btn-sm btn-outline-primary edit-leave-type"
            data-url="{{ route('admin.leave-types.show', $type) }}"
            data-update-url="{{ route('admin.leave-types.update', $type) }}"
            title="Edit">
        <i class="ti ti-pencil"></i>
    </button>
@endcan
@can('leave_management.delete')
    <button type="button" class="btn btn-sm btn-outline-danger delete-leave-type"
            data-url="{{ route('admin.leave-types.destroy', $type) }}"
            title="Delete">
        <i class="ti ti-trash"></i>
    </button>
@endcan
