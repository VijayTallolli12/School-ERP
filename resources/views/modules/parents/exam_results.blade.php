@extends('layouts.parent')

@section('title', 'Exam Results')
@section('page-title', 'Student Exam Results')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('parent-portal.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Exam Results</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Student Exam Results</h5>
        </div>
        <div class="card-body">
            @foreach($students as $student)
                <div class="mb-4">
                    <h6>{{ $student->full_name }} ({{ $student->admission_no }})</h6>
                    
                    @php
                        $examResults = \App\Modules\Exams\Models\ExamResult::with(['exam.subject'])
                            ->where('student_id', $student->id)
                            ->whereHas('exam', function ($query) {
                                $query->where('academic_year_id', app(\App\Core\Tenant\SchoolContext::class)->getAcademicYearId())
                                      ->where('is_published', true);
                            })
                            ->get()
                            ->groupBy('exam.exam_name');
                    @endphp

                    @if($examResults->isEmpty())
                        <p class="text-muted">No published exam results found for this academic year.</p>
                    @else
                        @foreach($examResults as $examName => $results)
                            <div class="card mb-3 border shadow-none">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">{{ $examName }}</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th>Maximum Marks</th>
                                                    <th>Pass Marks</th>
                                                    <th>Marks Obtained</th>
                                                    <th>Grade</th>
                                                    <th>Status</th>
                                                    <th>Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $totalMax = 0;
                                                    $totalObtained = 0;
                                                @endphp
                                                @foreach($results as $result)
                                                    @php
                                                        $totalMax += $result->exam->maximum_marks;
                                                        $totalObtained += $result->marks_obtained;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $result->exam->subject->name }}</td>
                                                        <td>{{ $result->exam->maximum_marks }}</td>
                                                        <td>{{ $result->exam->pass_marks }}</td>
                                                        <td>{{ $result->marks_obtained }}</td>
                                                        <td>{{ $result->grade ?? '-' }}</td>
                                                        <td>
                                                            @if($result->marks_obtained >= $result->exam->pass_marks)
                                                                <span class="badge bg-success">Pass</span>
                                                            @else
                                                                <span class="badge bg-danger">Fail</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $result->remarks ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="fw-bold">
                                                    <td>Total</td>
                                                    <td>{{ $totalMax }}</td>
                                                    <td>-</td>
                                                    <td>{{ $totalObtained }}</td>
                                                    <td colspan="3">
                                                        Percentage: {{ $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0 }}%
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                @if(!$loop->last)
                    <hr>
                @endif
            @endforeach
        </div>
    </div>
@endsection
