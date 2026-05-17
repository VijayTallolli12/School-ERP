<div class="btn-group btn-group-sm" role="group">
    @can('attendance.update')
        <button type="button" class="btn btn-outline-primary" onclick="editAttendance({{ $attendance->id }})" title="Edit">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @can('attendance.delete')
        <button type="button" class="btn btn-outline-danger delete-attendance" data-url="{{ route('admin.attendance.destroy', $attendance) }}" title="Delete">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
