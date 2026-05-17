<div class="btn-group" role="group">
    @can('teachers.update')
        <button type="button" class="btn btn-sm btn-outline-primary edit-leave" data-url="{{ route('admin.teachers.leaves.show', $leave) }}" data-update-url="{{ route('admin.teachers.leaves.update', $leave) }}">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @can('teachers.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-leave" data-url="{{ route('admin.teachers.leaves.destroy', $leave) }}">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
