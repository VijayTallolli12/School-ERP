@can('parents.update')
    <a href="javascript:void(0)" class="btn btn-sm btn-outline-secondary edit-parent"
       data-url="{{ route('admin.parents.show', $parent) }}"
       data-update-url="{{ route('admin.parents.update', $parent) }}" title="Edit">
        <i class="ti ti-pencil"></i>
    </a>
@endcan

@can('parents.delete')
    <a href="javascript:void(0)" class="btn btn-sm btn-outline-danger delete-parent ms-1"
       data-url="{{ route('admin.parents.destroy', $parent) }}" title="Delete">
        <i class="ti ti-trash"></i>
    </a>
@endcan