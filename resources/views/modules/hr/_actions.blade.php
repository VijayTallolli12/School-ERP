<div class="btn-group" role="group">
    @can('hr.update')
        <button type="button" class="btn btn-sm btn-outline-primary edit-employee" data-url="{{ route('admin.hr.show', $employee) }}" data-update-url="{{ route('admin.hr.update', $employee) }}" title="Edit">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @can('hr.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-employee" data-url="{{ route('admin.hr.destroy', $employee) }}" title="Delete">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
