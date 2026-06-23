@php
    $map = [
        'pending' => ['bg-secondary', 'Pending'],
        'running' => ['bg-info', 'Running'],
        'completed' => ['bg-success', 'Completed'],
        'failed' => ['bg-danger', 'Failed'],
    ];
    [$class, $label] = $map[$status] ?? ['bg-secondary', $status];
@endphp
<span class="badge {{ $class }}">{{ $label }}</span>
