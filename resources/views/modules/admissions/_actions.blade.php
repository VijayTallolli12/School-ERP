<x-erp.table-action-menu>
    <li>
        <a href="{{ route('admin.admissions.show', $a) }}" class="dropdown-item d-flex align-items-center" title="View">
            <i class="ti ti-eye me-2"></i> View
        </a>
    </li>
    @if (in_array($a->status, ['approved', 'verified'], true) && auth()->user()->can('admissions.convert'))
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center convert-admission" title="Convert to Student"
                    data-url="{{ route('admin.admissions.convert', $a) }}">
                <i class="ti ti-user-check me-2"></i> Convert to Student
            </button>
        </li>
    @endif
    @if (auth()->user()->can('admissions.delete'))
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-admission" title="Delete"
                    data-url="{{ route('admin.admissions.destroy', $a) }}">
                <i class="ti ti-trash me-2"></i> Delete
            </button>
        </li>
    @endif
</x-erp.table-action-menu>
