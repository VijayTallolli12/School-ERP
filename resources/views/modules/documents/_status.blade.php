@if ($doc->is_verified)
    <span class="badge bg-success" title="Verified at {{ $doc->verified_at?->format('d M Y h:i A') }}">
        <i class="ti ti-shield-check me-1"></i>Verified
    </span>
@else
    <span class="badge bg-warning text-dark">
        <i class="ti ti-clock me-1"></i>Pending
    </span>
@endif
