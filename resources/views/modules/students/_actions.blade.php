<div class="table-actions">
    @can('students.update')
        <button type="button" class="btn btn-sm btn-outline-primary edit-student"
                data-url="{{ route('admin.students.show', $student) }}"
                data-update-url="{{ route('admin.students.update', $student) }}">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @can('students.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-student" data-url="{{ route('admin.students.destroy', $student) }}">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
