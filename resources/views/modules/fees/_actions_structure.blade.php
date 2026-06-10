<div class="btn-group btn-group-sm">
    @can('fees.update')
        <button type="button" class="btn btn-outline-primary edit-fee-structure" data-url="{{ route('admin.fees.structures.show', $row) }}" data-update-url="{{ route('admin.fees.structures.update', $row) }}">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @can('fees.delete')
        <button type="button" class="btn btn-outline-danger delete-fee-structure" data-url="{{ route('admin.fees.structures.destroy', $row) }}">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
