@php
    $map = ['new' => 'danger', 'acknowledged' => 'warning', 'resolved' => 'success'];
    $labels = ['new' => 'New', 'acknowledged' => 'Acknowledged', 'resolved' => 'Resolved'];
@endphp
<span class="badge bg-{{ $map[$sos->status] ?? 'secondary' }}">{{ $labels[$sos->status] ?? $sos->status }}</span>