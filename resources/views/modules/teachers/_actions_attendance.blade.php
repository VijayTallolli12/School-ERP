<div class="btn-group" role="group">
    @can('teachers.update')
        <button type="button" class="btn btn-sm btn-outline-primary edit-attendance" data-url="{{ route('admin.teachers.attendance.show', $attendance) }}" data-update-url="{{ route('admin.teachers.attendance.update', $attendance) }}">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @can('teachers.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-attendance" data-url="{{ route('admin.teachers.attendance.destroy', $attendance) }}">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
