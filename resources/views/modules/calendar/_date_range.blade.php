<div class="text-nowrap small">
    @if ($event->start_date && $event->end_date && $event->start_date->format('Y-m-d') !== $event->end_date->format('Y-m-d'))
        {{ $event->start_date->format('d M Y') }} - {{ $event->end_date->format('d M Y') }}
    @else
        {{ $event->start_date?->format('d M Y') ?? '-' }}
    @endif
</div>
