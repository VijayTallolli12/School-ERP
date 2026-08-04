{{--
    ERP Stat Card (Gold Standard: modules.dashboard.index / .erp-hero-card)
    Props:
        label      (string)  Card label
        value      (string)  Card value (already formatted)
        icon       (string)  Tabler icon name WITHOUT the `ti ti-` prefix
        color      (string)  primary|success|warning|danger|info|secondary|dark (default: primary)
        trend      (string)  up|down|neutral|null
        trendValue (string)  optional trend text
        route      (string)  optional URL to link the card
        cols       (string)  column classes wrapper (default: col-xl-3 col-md-6)
--}}
@php
    $iconColor = in_array($color ?? 'primary', ['primary','success','warning','danger','info','secondary','dark'])
        ? $color
        : 'primary';
@endphp
<div class="{{ $cols ?? 'col-xl-3 col-md-6' }}">
    @if($route ?? null)
        <a href="{{ $route }}" class="text-decoration-none">
    @endif
    <div class="erp-hero-card">
        <div>
            <div class="hero-value">{{ $value }}</div>
            <div class="hero-label">{{ $label }}</div>
            @if($trend ?? null)
                <div class="hero-trend trend-{{ in_array($trend, ['up','down']) ? $trend : 'neutral' }}">
                    <i class="ti ti-arrow-{{ in_array($trend, ['up','down']) ? $trend : 'right' }}"></i>
                    @if($trendValue ?? null)
                        {{ $trendValue }}
                    @endif
                </div>
            @endif
        </div>
        @if($icon ?? null)
            <div class="hero-icon {{ $iconColor }}">
                <i class="ti ti-{{ $icon }}"></i>
            </div>
        @endif
    </div>
    @if($route ?? null)
        </a>
    @endif
</div>
