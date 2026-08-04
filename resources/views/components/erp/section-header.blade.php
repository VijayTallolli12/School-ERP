{{--
    ERP Card Section Header
    Standard header row: optional icon + title on left, slot (buttons) on right.

    Props:
        icon  (string)  Tabler icon name WITHOUT prefix (default: null)
        title (string)  Header title
        color (string)  icon color modifier (default: primary)
        size  (string)  heading size class (default: fw-semibold mb-0)
--}}
<div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <h3 class="card-title {{ $size ?? 'fw-semibold mb-0' }}">
        @if($icon ?? null)
            <i class="ti ti-{{ $icon }} text-{{ $color ?? 'primary' }} me-2"></i>
        @endif
        {{ $title }}
    </h3>
    @isset($actions)
        <div class="d-flex align-items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
