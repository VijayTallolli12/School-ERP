{{--
    ERP Loading State (skeleton)
    Props:
        rows (int)  number of skeleton rows (default: 5)
--}}
<div class="skeleton-card p-3" role="status" aria-label="Loading">
    @for($i = 0; $i < ($rows ?? 5); $i++)
        <div class="skeleton-row d-flex gap-3 align-items-center mb-3">
            <div class="skeleton skeleton-text w-25"></div>
            <div class="skeleton skeleton-text w-50"></div>
            <div class="skeleton skeleton-text w-25"></div>
        </div>
    @endfor
    <span class="visually-hidden">Loading...</span>
</div>
