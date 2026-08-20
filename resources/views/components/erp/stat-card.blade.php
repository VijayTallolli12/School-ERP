{{--
    ERP Stat Card (KPI Card - 02 Design)
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
    <div class="card h-100 border-0" style="border-radius: var(--erp-card-radius, 14px); box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid var(--erp-gray-200, #e2e8f0) !important;">
        <div class="card-body p-4 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--erp-gray-500, #64748b); text-transform: uppercase; letter-spacing: 0.5px;">
                    {{ $label }}
                </div>
                @if($icon ?? null)
                    <div class="d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 8px; background-color: var(--bs-{{ $iconColor }}-bg-subtle, #eef2ff); color: var(--erp-{{ $iconColor }}, var(--bs-{{ $iconColor }}, #4f46e5));">
                        <i class="ti ti-{{ $icon }}" style="font-size: 1.25rem;"></i>
                    </div>
                @else
                    <div></div>
                @endif
            </div>

            <div style="font-size: 2rem; font-weight: 700; color: var(--erp-gray-900, #0f172a); line-height: 1.2; letter-spacing: -0.5px; margin-bottom: 0.5rem;">
                {{ $value }}
            </div>
            
            <div class="d-flex align-items-center" style="font-size: 0.85rem; font-weight: 500;">
                @if($trend ?? null)
                    @php
                        $isUp = $trend === 'up';
                        $isDown = $trend === 'down';
                        $trendColor = $isUp ? '#16a34a' : ($isDown ? '#dc2626' : '#64748b');
                        $trendIcon = $isUp ? 'trending-up' : ($isDown ? 'trending-down' : 'minus');
                    @endphp
                    <span style="color: {{ $trendColor }}; display: flex; align-items: center;">
                        <i class="ti ti-{{ $trendIcon }} me-1"></i>
                        @if($trendValue ?? null)
                            {{ $trendValue }}
                        @else
                            {{ $isUp ? 'Increased' : ($isDown ? 'Decreased' : 'Stable') }}
                        @endif
                    </span>
                @else
                    <span style="color: #64748b; display: flex; align-items: center;">
                        <i class="ti ti-minus me-1"></i> Stable
                    </span>
                @endif
            </div>
        </div>
    </div>
    @if($route ?? null)
        </a>
    @endif
</div>
