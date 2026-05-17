@can('exams.update')
    <button type="button" class="btn btn-sm btn-outline-primary edit-result" data-url="{{ route('admin.exams.results.show', $result) }}" data-update-url="{{ route('admin.exams.results.update', $result) }}">
        <i class="ti ti-pencil"></i>
    </button>
@endcan

@can('exams.delete')
    <button type="button" class="btn btn-sm btn-outline-danger delete-result" data-url="{{ route('admin.exams.results.destroy', $result) }}">
        <i class="ti ti-trash"></i>
    </button>
@endcan
