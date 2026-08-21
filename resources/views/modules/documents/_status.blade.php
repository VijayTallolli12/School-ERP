@if ($doc->is_verified)
    <span class="badge bg-success-subtle text-success" title="Verified at {{ $doc->verified_at?->format('d M Y h:i A') }}">
        <i class="ti ti-shield-check me-1"></i>Verified
    </span>
@else
    <span class="badge bg-warning-subtle text-warning">
        <i class="ti ti-clock me-1"></i>Pending
    </span>
@endif
