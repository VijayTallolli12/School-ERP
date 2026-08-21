<x-erp.table-action-menu>
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
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center edit-library"
                        data-type="{{ $type }}"
                        data-url="{{ route('admin.library.'.$routeBase.'.show', $model) }}"
                        data-update-url="{{ route('admin.library.'.$routeBase.'.update', $model)">
                    <i class="ti ti-pencil me-2"></i> Edit
                </button>
            </li>
        @endcan
    @endif
    @if ($type === 'issue' && $model->status === 'issued')
        @can('library.update')
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center return-book"
                        data-url="{{ route('admin.library.issues.return', $model) }}">
                    <i class="ti ti-arrow-back-up me-2"></i> Return Book
                </button>
            </li>
        @endcan
    @endif
    @can('library.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-library"
                    data-url="{{ route('admin.library.'.$routeBase.'.destroy', $model) }}">
                <i class="ti ti-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
