@extends('layouts.admin')

@section('title', 'Print Class Timetable')
@section('page-title', 'Class Timetable')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.timetable.index') }}">Timetable</a></li>
    <li class="breadcrumb-item active">Print Class Timetable</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="fw-semibold card-title mb-0">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</h3>
            <span class="badge bg-primary">{{ $academicYear->name }}</span>
        </div>
        <div class="card-body">
            @if($schedule->isEmpty())
                <div class="alert alert-secondary">No timetable slots found for this class and academic year.</div>
            @else
                @foreach($schedule as $day => $slots)
                    <div class="mb-4">
                        <h5 class="text-secondary">{{ $day }}</h5>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Period</th>
                                <th>Time</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Room</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($slots as $slot)
                                <tr>
                                    <td>{{ $slot->period_number }} - {{ $slot->period_label }}</td>
                                    <td>{{ $slot->time_range }}</td>
                                    <td>{{ $slot->subject?->name }}</td>
                                    <td>{{ $slot->teacher?->full_name }}</td>
                                    <td>{{ $slot->room ?? '-' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection
