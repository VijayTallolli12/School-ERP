<div class="table-actions d-flex gap-1">
    <a href="{{ route('admin.admissions.show', $a) }}" class="btn btn-sm btn-outline-primary" title="View">
        <i class="ti ti-eye"></i>
    </a>
    @if (in_array($a->status, ['approved', 'verified'], true) && auth()->user()->can('admissions.convert'))
        <button type="button" class="btn btn-sm btn-outline-success convert-admission" title="Convert to Student"
                data-url="{{ route('admin.admissions.convert', $a) }}">
            <i class="ti ti-user-check"></i>
        </button>
    @endif
    @if (auth()->user()->can('admissions.delete'))
        <button type="button" class="btn btn-sm btn-outline-danger delete-admission" title="Delete"
                data-url="{{ route('admin.admissions.destroy', $a) }}">
            <i class="ti ti-trash"></i>
        </button>
    @endif
</div>
