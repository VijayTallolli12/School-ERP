<div class="table-actions d-flex gap-1">
    @php
        $routeBase = [
            'book' => 'books',
            'category' => 'categories',
            'author' => 'authors',
            'publisher' => 'publishers',
            'issue' => 'issues',
            'fine-setting' => 'fine-settings',
        ][$type] ?? $type;
    @endphp
    @if ($type !== 'issue')
        @can('library.update')
            <button type="button" class="btn btn-sm btn-outline-primary edit-library"
                    data-type="{{ $type }}"
                    data-url="{{ route('admin.library.'.$routeBase.'.show', $model) }}"
                    data-update-url="{{ route('admin.library.'.$routeBase.'.update', $model) }}">
                <i class="ti ti-pencil"></i>
            </button>
        @endcan
    @endif
    @if ($type === 'issue' && $model->status === 'issued')
        @can('library.update')
            <button type="button" class="btn btn-sm btn-outline-success return-book"
                    data-url="{{ route('admin.library.issues.return', $model) }}">
                <i class="ti ti-arrow-back-up"></i>
            </button>
        @endcan
    @endif
    @can('library.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-library"
                data-url="{{ route('admin.library.'.$routeBase.'.destroy', $model) }}">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
