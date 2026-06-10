@can('exams.update')
    <button type="button" class="btn btn-sm btn-outline-primary edit-exam" data-url="{{ route('admin.exams.show', $exam) }}" data-update-url="{{ route('admin.exams.update', $exam) }}" title="Edit">
        <i class="ti ti-pencil"></i>
    </button>
@endcan

@can('exams.publish')
    <button type="button" class="btn btn-sm btn-outline-info publish-exam" data-url="{{ route('admin.exams.publish', $exam) }}" title="{{ $exam->is_published ? 'Unpublish' : 'Publish' }}">
        <i class="ti ti-{{ $exam->is_published ? 'eye-off' : 'eye' }}"></i>
    </button>
@endcan

@can('exams.delete')
    <button type="button" class="btn btn-sm btn-outline-danger delete-exam" data-url="{{ route('admin.exams.destroy', $exam) }}" title="Delete">
        <i class="ti ti-trash"></i>
    </button>
@endcan
