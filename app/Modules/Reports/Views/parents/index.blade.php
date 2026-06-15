@extends('layouts.admin')

@section("title", "Parent Reports Dashboard")
@section("page-title", "Parent Reports Dashboard")

@section("content")
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Parents</h5>
                    <p class="card-text display-4">{{ $stats['total_parents'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Active Parents</h5>
                    <p class="card-text display-4">{{ $stats['active_parents'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Mapped Parents</h5>
                    <p class="card-text display-4">{{ $stats['mapped_parents'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Linked Students</h5>
                    <p class="card-text display-4">{{ $stats['linked_students'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4><i class="ti ti-list text-primary me-2"></i>Available Reports</h4>
        </div>
        <div class="card-body">
            <div class="list-group">
                <a href="{{ route('reports.parents.list') }}" class="list-group-item list-group-item-action">
                    <h5 class="mb-1">Parent List Report</h5>
                    <p class="mb-1">View parent contact, occupation, status, linked student, and class details.</p>
                </a>
                <a href="{{ route('reports.parents.mapping') }}" class="list-group-item list-group-item-action">
                    <h5 class="mb-1">Parent Student Mapping Report</h5>
                    <p class="mb-1">Review parent-student relationships and primary contact assignments.</p>
                </a>
                <a href="{{ route('reports.parents.activity_summary') }}" class="list-group-item list-group-item-action">
                    <h5 class="mb-1">Parent Activity Summary Report</h5>
                    <p class="mb-1">Summarize notifications and portal activity signals by parent.</p>
                </a>
            </div>
        </div>
    </div>
@endsection
