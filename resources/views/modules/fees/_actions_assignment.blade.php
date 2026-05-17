<div class="btn-group btn-group-sm">
    @can('fees.update')
        <button type="button" class="btn btn-outline-secondary edit-assignment" data-url="{{ route('admin.fees.assignments.show', $row) }}" data-update-url="{{ route('admin.fees.assignments.update', $row) }}">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @can('fees.delete')
        <button type="button" class="btn btn-outline-danger delete-assignment" data-url="{{ route('admin.fees.assignments.destroy', $row) }}">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
