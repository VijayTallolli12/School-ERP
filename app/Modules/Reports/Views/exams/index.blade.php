@extends('layouts.admin')

@section("title", "Exam Reports Dashboard")
@section("page-title", "Exam Reports Dashboard")

@section("content")
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Exams</h5>
                    <p class="card-text display-4">{{ $stats['total_exams'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Published Results</h5>
                    <p class="card-text display-4">{{ $stats['published_results'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Pass Percentage</h5>
                    <p class="card-text display-4">{{ $stats['pass_percentage'] ?? 0 }}%</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Toppers Count</h5>
                    <p class="card-text display-4">{{ $stats['toppers_count'] ?? 0 }}</p>
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
                        <a href="{{ route('reports.exams.results') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Exam Results Report</h5>
                            <p class="mb-1">View comprehensive list of exam results with filtering options.</p>
                        </a>
                        <a href="{{ route('reports.exams.class_performance') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Class Performance Report</h5>
                            <p class="mb-1">Analyze performance averages and pass/fail ratios by class and section.</p>
                        </a>
                        <a href="{{ route('reports.exams.subject_performance') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Subject Performance Report</h5>
                            <p class="mb-1">Analyze subject-wise highest, lowest and average marks.</p>
                        </a>
                        <a href="{{ route('reports.exams.student_summary') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Student Result Summary</h5>
                            <p class="mb-1">Get an overall report card summary for a specific student.</p>
                        </a>
                        <a href="{{ route('reports.exams.top_performers') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Top Performers</h5>
                            <p class="mb-1">View ranked list of top performing students based on exam percentages.</p>
                        </a>
                        <a href="{{ route('reports.exams.pass_fail_analysis') }}" class="list-group-item list-group-item-action">
                            <h5 class="mb-1">Pass/Fail Analysis</h5>
                            <p class="mb-1">Analyze pass/fail rates across classes and subjects with student-level breakdown.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection