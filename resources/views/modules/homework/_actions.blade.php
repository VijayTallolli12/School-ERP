<div class="btn-group btn-group-sm">
@can('homework.update')
    <button type="button" class="btn btn-sm btn-outline-primary edit-homework"
            data-url="{{ route('admin.homework.show', $hw) }}"
            data-update-url="{{ route('admin.homework.update', $hw) }}"
            title="Edit">
        <i class="ti ti-pencil"></i>
    </button>
@endcan
@can('homework.delete')
    <button type="button" class="btn btn-sm btn-outline-danger delete-homework"
            data-url="{{ route('admin.homework.destroy', $hw) }}"
            title="Delete">
        <i class="ti ti-trash"></i>
    </button>
@endcan
</div>
