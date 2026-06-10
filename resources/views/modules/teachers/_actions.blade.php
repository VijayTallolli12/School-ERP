<div class="btn-group" role="group">
    @can('teachers.update')
        <button type="button" class="btn btn-sm btn-outline-primary edit-teacher" data-url="{{ route('admin.teachers.show', $teacher) }}" data-update-url="{{ route('admin.teachers.update', $teacher) }}" title="Edit">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @can('teachers.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-teacher" data-url="{{ route('admin.teachers.destroy', $teacher) }}" title="Delete">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
