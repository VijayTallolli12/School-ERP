<x-erp.table-action-menu>
@can('exams.update')
    <li>
        <button type="button" class="dropdown-item d-flex align-items-center edit-exam" data-url="{{ route('admin.exams.show', $exam) }}" data-update-url="{{ route('admin.exams.update', $exam) }}" title="Edit">
            <i class="ti ti-pencil me-2"></i> Edit
        </button>
    </li>
@endcan

@can('exams.publish')
    <li>
        <button type="button" class="dropdown-item d-flex align-items-center publish-exam" data-url="{{ route('admin.exams.publish', $exam) }}" title="{{ $exam->is_published ? 'Unpublish' : 'Publish' }}">
            <i class="ti ti-{{ $exam->is_published ? 'eye-off' : 'eye' }} me-2"></i> {{ $exam->is_published ? 'Unpublish' : 'Publish' }}
        </button>
    </li>
@endcan

@can('exams.delete')
    <li>
        <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-exam" data-url="{{ route('admin.exams.destroy', $exam) }}" title="Delete">
            <i class="ti ti-trash me-2 text-danger"></i> Delete
        </button>
    </li>
@endcan
</x-erp.table-action-menu>
