@props([
    'id',
    'title',
    'subtitle' => null,
    'formId' => null,
    'action' => '#',
    'method' => 'POST',
    'width' => '700px',
    'saveButtonText' => 'Save',
    'saveButtonId' => null,
    'multipart' => false,
    'hasTabs' => false,
    'showFooter' => true
])

<div class="offcanvas offcanvas-end shadow-lg erp-side-panel" tabindex="-1" id="{{ $id }}" aria-labelledby="{{ $id }}Title" style="width: 100%; max-width: {{ $width }};">
    @if($formId)
        <form class="ajax-form d-flex flex-column h-100 mb-0" 
              id="{{ $formId }}" 
              method="{{ $method !== 'GET' ? 'POST' : 'GET' }}" 
              action="{{ $action }}"
              @if($multipart) enctype="multipart/form-data" @endif>
            @csrf
            @if(!in_array($method, ['GET', 'POST']))
                @method($method)
            @endif
    @else
        <div class="d-flex flex-column h-100">
    @endif
    
        <div class="offcanvas-header d-block {{ $hasTabs ? 'pb-0 border-bottom-0' : 'border-bottom' }}">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1 pe-3">
                    <h4 class="offcanvas-title fw-bold text-dark" id="{{ $id }}Title" style="line-height: 1.2;">{{ $title }}</h4>
                    @if($subtitle)
                        <p class="text-muted small mb-0 mt-1">{{ $subtitle }}</p>
                    @endif
                </div>
                <button type="button" class="btn-close p-2 mt-1 shadow-none" data-bs-dismiss="offcanvas" aria-label="Close panel"></button>
            </div>
            
            @if($hasTabs && isset($tabs))
                <div class="w-100 mt-4">
                    {{ $tabs }}
                </div>
            @endif
        </div>
        
        <div class="offcanvas-body flex-grow-1 overflow-y-auto p-4 px-sm-5">
            {{ $slot }}
        </div>
        
        @if($showFooter)
            <div class="offcanvas-footer border-top p-3 bg-light d-flex justify-content-end gap-2 mt-auto">
                <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="offcanvas">Cancel</button>
                @if($formId)
                    <button type="submit" class="btn btn-primary px-4" @if($saveButtonId) id="{{ $saveButtonId }}" @endif>{{ $saveButtonText }}</button>
                @endif
            </div>
        @endif
        
    @if($formId)
        </form>
    @else
        </div>
    @endif
</div>
