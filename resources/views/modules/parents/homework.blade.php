@extends('layouts.parent')

@section('title', 'Homework')
@section('page-title', 'Homework')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('parent-portal.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Homework</li>
@endsection

@section('content')
    <div class="row g-4">
        @forelse($homework as $entry)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary">{{ $entry->subject?->name ?? 'N/A' }}</span>
                            @php
                                $isOverdue = $entry->due_date?->isPast() && $entry->status === 'active';
                                $isDueSoon = $entry->due_date?->isFuture() && $entry->due_date?->diffInDays(now()) <= 2;
                            @endphp
                            @if($entry->status === 'inactive')
                                <span class="badge bg-secondary">Completed</span>
                            @elseif($isOverdue)
                                <span class="badge bg-danger">Overdue</span>
                            @elseif($isDueSoon)
                                <span class="badge bg-warning">Due Soon</span>
                            @else
                                <span class="badge bg-success">Pending</span>
                            @endif
                        </div>

                        <h5 class="card-title mb-1">{{ $entry->title }}</h5>

                        @if($entry->description)
                            <p class="card-text text-muted small mb-3 flex-grow-1">{{ Str::limit($entry->description, 120) }}</p>
                        @else
                            <div class="flex-grow-1"></div>
                        @endif

                        <div class="mt-auto">
                            <div class="d-flex justify-content-between text-muted small mb-2">
                                <span>
                                    <i class="ti ti-calendar me-1"></i> Assigned: {{ $entry->assigned_date?->format('M d, Y') }}
                                </span>
                                <span class="{{ $isOverdue ? 'text-danger fw-semibold' : '' }}">
                                    <i class="ti ti-clock me-1"></i> Due: {{ $entry->due_date?->format('M d, Y') }}
                                </span>
                            </div>

                            @if($entry->attachment_url)
                                <a href="{{ $entry->attachment_url }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="ti ti-download me-1"></i> Download Attachment
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="erp-empty-state">
                            <i class="ti ti-books"></i>
                            <h5>No Homework Assigned</h5>
                            <p>There is no homework assigned to your children at this time.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection
