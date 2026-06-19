@extends('layouts.parent')

@section('title', 'Timetable')
@section('page-title', 'Student Timetable')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.parent-portal.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Timetable</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-calendar text-primary me-2"></i>Student Timetable</h5>
        </div>
        <div class="card-body">
            @foreach($students as $student)
                <div class="mb-4">
                    <h6>{{ $student->full_name }} ({{ $student->admission_no }})</h6>
                    
                    @php
                        $session = $student->sessions()->where('status', 'active')->first();
                        $classSectionId = $session ? $session->class_section_id : null;
                        
                        $timetable = [];
                        if ($classSectionId) {
                            $timetableRecords = \App\Modules\Timetable\Models\Timetable::with(['subject', 'teacher'])
                                ->where('class_section_id', $classSectionId)
                                ->where('academic_year_id', app(\App\Core\Tenant\SchoolContext::class)->getAcademicYearId())
                                ->orderBy('start_time')
                                ->get();
                                
                            foreach ($timetableRecords as $record) {
                                $timetable[$record->day_of_week][] = $record;
                            }
                        }
                        
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    @endphp

                    @if(!$classSectionId)
                        <p class="text-muted">Student is not assigned to any active class section.</p>
                    @elseif(empty($timetable))
                        <p class="text-muted">No timetable found for this class section.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="15%">Day</th>
                                        <th>Periods</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($days as $day)
                                        <tr>
                                            <td class="fw-bold align-middle">{{ $day }}</td>
                                            <td>
                                                @if(isset($timetable[$day]))
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($timetable[$day] as $period)
                                                            <div class="card border shadow-none mb-0" style="min-width: 150px;">
                                                                <div class="card-body p-2 text-center">
                                                                    <div class="small text-muted mb-1">
                                                                        {{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }} - 
                                                                        {{ \Carbon\Carbon::parse($period->end_time)->format('h:i A') }}
                                                                    </div>
                                                                    <div class="fw-bold">{{ $period->subject->name }}</div>
                                                                    <div class="small text-muted">{{ $period->teacher->full_name ?? 'N/A' }}</div>
                                                                    @if($period->room_number)
                                                                        <div class="small text-muted">Room: {{ $period->room_number }}</div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted">No classes scheduled</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                @if(!$loop->last)
                    <hr>
                @endif
            @endforeach
        </div>
    </div>
@endsection
