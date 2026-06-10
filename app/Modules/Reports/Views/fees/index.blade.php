@extends('layouts.admin')

@section("title", "Fee Reports Dashboard")
@section("page-title", "Fee Reports Dashboard")

@section("content")
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Collected</h5>
                    <p class="card-text display-4">{{ number_format($stats['total_collected'] ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Pending Fees</h5>
                    <p class="card-text display-4">{{ number_format($stats['pending_fees'] ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Monthly Collection</h5>
                    <p class="card-text display-4">{{ number_format($stats['monthly_collection'] ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Available Reports</h4>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('reports.fees.paid') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Fee Collection Report</h5>
                            <p class="mb-1">View detailed records of all fee payments collected, with options to filter by date range, class section, and payment mode.</p>
                        </a>
                        <a href="{{ route('reports.fees.pending') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Pending Fees Report</h5>
                            <p class="mb-1">Track outstanding fee balances for students, filtered by academic year.</p>
                        </a>
                        <a href="{{ route('reports.fees.overdue') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Overdue Fees Report</h5>
                            <p class="mb-1">Identify past-due fee assignments and monitor overdue balances across the academic year.</p>
                        </a>
                        <a href="{{ route('reports.fees.collection_summary') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Collection Summary</h5>
                            <p class="mb-1">Get an aggregated view of fee demands, payments, and balances summarized by class and section.</p>
                        </a>
                        <a href="{{ route('reports.fees.defaulters') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Fee Defaulters</h5>
                            <p class="mb-1">Identify defaulters with outstanding balances, overdue days, and parent contact details.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
