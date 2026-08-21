@extends('layouts.admin')

@section('title', 'Print Teacher Timetable')
@section('page-title', 'Teacher Timetable')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.timetable.index') }}">Timetable</a></li>
    <li class="breadcrumb-item active">Print Teacher Timetable</li>
@endsection

@section('content')
    <style>
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            .app-header, .app-sidebar, .app-footer, .app-content-header { display: none !important; }
            .app-main { margin: 0 !important; padding: 0 !important; }
            body { background: #fff !important; font-size: 11px !important; }
            .card { border: none !important; box-shadow: none !important; margin: 0 !important; }
            .card-header { display: none !important; }
            .card-body { padding: 0 !important; }
            
            /* Compact Table */
            .table th, .table td { padding: 0.25rem 0.1rem !important; }
            .table th .fw-bold { font-size: 11px !important; }
            .table th small { font-size: 9px !important; }
            .table td .fw-semibold { font-size: 11px !important; margin-bottom: 0 !important; }
            .table td .small { font-size: 9px !important; margin-top: 0 !important; }
            
            /* Compact Header & Footer */
            .d-print-block.mb-4 { margin-bottom: 0.5rem !important; padding-bottom: 0.25rem !important; }
            .d-print-block h2 { font-size: 16px !important; margin-bottom: 0 !important; }
            .d-print-block h4, .d-print-block h3 { font-size: 14px !important; margin-bottom: 0 !important; }
            
            .d-print-block.mt-5 { margin-top: 1rem !important; padding-top: 0 !important; }
            .d-print-block .row.mt-5 { margin-top: 1.5rem !important; }
            .d-print-block hr { margin-bottom: 0.25rem !important; margin-top: 0 !important; }
        }
    </style>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h3 class="fw-semibold card-title mb-0 d-inline-block">{{ $teacher->full_name }}</h3>
                <span class="badge bg-primary ms-2">{{ $academicYear->name }}</span>
            </div>
            <div class="d-print-none">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="ti ti-printer me-1"></i> Print
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Print Header -->
            <div class="d-none d-print-block text-center mb-4 pb-3 border-bottom">
                <h2 class="mb-1 fw-bold">{{ setting('school_name', 'School ERP') }}</h2>
                <h4 class="mb-2 text-muted">Teacher Timetable ({{ $academicYear->name }})</h4>
                <h3 class="mb-0 fw-semibold">{{ $teacher->full_name }}</h3>
            </div>
            @if($schedule->isEmpty())
                <div class="alert alert-secondary">No timetable slots found for this teacher and academic year.</div>
            @else
                @php
                    $allSlots = collect();
                    foreach($schedule as $daySlots) {
                        $allSlots = $allSlots->merge($daySlots);
                    }
                    $periods = $allSlots->unique('period_number')->sortBy('period_number');
                    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $activeDays = collect($daysOfWeek)->filter(fn($d) => $schedule->has($d))->values();
                @endphp
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center align-middle" style="min-width: 800px;">
                        <thead class="table-light">
                        <tr>
                            <th width="12%" class="text-uppercase fw-bold text-muted">Day</th>
                            @foreach($periods as $period)
                                <th>
                                    <div class="fw-bold">{{ $period->period_label ?: 'Period ' . $period->period_number }}</div>
                                    <small class="text-muted fw-normal" style="font-size: 0.75rem;">{{ $period->time_range }}</small>
                                </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($activeDays as $day)
                            <tr>
                                <th class="table-light text-start">{{ $day }}</th>
                                @foreach($periods as $period)
                                    @php
                                        $slot = $schedule->get($day)?->firstWhere('period_number', $period->period_number);
                                    @endphp
                                    <td>
                                        @if($slot)
                                            <div class="fw-semibold text-dark mb-1">{{ $slot->classSection?->schoolClass->name ?? '-' }} - {{ $slot->classSection?->section->name ?? '-' }}</div>
                                            <div class="small text-muted">{{ $slot->subject?->name }}</div>
                                            @if($slot->room)
                                                <div class="small text-muted mt-1" style="font-size: 0.7rem;">Room: {{ $slot->room }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Print Footer -->
            <div class="d-none d-print-block mt-5 pt-5">
                <div class="row text-center mt-5">
                    <div class="col-6">
                        <hr class="w-50 mx-auto border-dark" style="border-width: 2px;">
                        <div class="fw-semibold mt-2">Teacher Signature</div>
                    </div>
                    <div class="col-6">
                        <hr class="w-50 mx-auto border-dark" style="border-width: 2px;">
                        <div class="fw-semibold mt-2">Principal</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
