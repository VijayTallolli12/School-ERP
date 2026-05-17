<?php

namespace App\Modules\Reports\Repositories;

use App\Core\Tenant\SchoolContext;
use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Models\ExamResult;
use App\Modules\Students\Models\Student;
use Illuminate\Support\Facades\DB;

class ExamReportRepository implements ExamReportRepositoryInterface
{
    public function __construct(private readonly SchoolContext $schoolContext) {}

    public function dashboardStats(): array
    {
        $schoolId = $this->schoolContext->id();

        $totalExams = Exam::where('school_id', $schoolId)->count();
        $publishedResults = Exam::where('school_id', $schoolId)->where('is_published', true)->count();

        $results = ExamResult::where('school_id', $schoolId)->get();
        $totalResults = $results->count();
        $passedResults = $results->where('status', 'pass')->count();
        
        $passPercentage = $totalResults > 0 ? round(($passedResults / $totalResults) * 100, 2) : 0;

        // Count unique students who got rank 1 or highest grade, for simplicity we count top scorers per exam
        $toppersCount = ExamResult::where('school_id', $schoolId)
            ->whereIn('id', function($query) use ($schoolId) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('exam_results')
                    ->where('school_id', $schoolId)
                    ->groupBy('exam_id');
            })->count();

        return [
            'total_exams' => $totalExams,
            'published_results' => $publishedResults,
            'pass_percentage' => $passPercentage,
            'toppers_count' => $toppersCount,
        ];
    }

    public function examResults(?int $academicYearId, ?int $examId, ?int $classSectionId, ?int $subjectId): array
    {
        $query = ExamResult::with(['exam.academicYear', 'exam.classSection.schoolClass', 'exam.classSection.section', 'exam.subject', 'student'])
            ->where('school_id', $this->schoolContext->id());

        if ($examId) {
            $query->where('exam_id', $examId);
        } else {
            if ($academicYearId) {
                $query->whereHas('exam', fn($q) => $q->where('academic_year_id', $academicYearId));
            }
            if ($classSectionId) {
                $query->whereHas('exam', fn($q) => $q->where('class_section_id', $classSectionId));
            }
            if ($subjectId) {
                $query->whereHas('exam', fn($q) => $q->where('subject_id', $subjectId));
            }
        }

        return $query->orderByDesc('marks_obtained')->limit(5000)->get()->map(function (ExamResult $r) {
            return [
                'student' => $r->student?->full_name,
                'admission_no' => $r->student?->admission_no,
                'exam_name' => $r->exam?->exam_name,
                'class_section' => $r->exam?->classSection ? $r->exam->classSection->schoolClass->name . ' - ' . $r->exam->classSection->section->name : '',
                'subject' => $r->exam?->subject?->name,
                'marks_obtained' => $r->marks_obtained,
                'maximum_marks' => $r->exam?->maximum_marks,
                'grade' => $r->grade,
                'status' => ucfirst($r->status),
            ];
        })->all();
    }

    public function classPerformance(?int $academicYearId, ?int $examId): array
    {
        $query = Exam::with(['classSection.schoolClass', 'classSection.section', 'results'])
            ->where('school_id', $this->schoolContext->id());

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        if ($examId) {
            $query->where('id', $examId);
        }

        $exams = $query->get();
        $performance = [];

        foreach ($exams as $exam) {
            $classLabel = $exam->classSection ? $exam->classSection->schoolClass->name . ' - ' . $exam->classSection->section->name : 'Unknown';
            $examName = $exam->exam_name;
            $key = $classLabel . '|' . $examName;

            if (!isset($performance[$key])) {
                $performance[$key] = [
                    'class_section' => $classLabel,
                    'exam_name' => $examName,
                    'total_students' => 0,
                    'passed' => 0,
                    'failed' => 0,
                    'total_marks' => 0,
                    'max_marks_sum' => 0,
                ];
            }

            $results = $exam->results;
            foreach ($results as $result) {
                $performance[$key]['total_students']++;
                if ($result->status === 'pass') {
                    $performance[$key]['passed']++;
                } else {
                    $performance[$key]['failed']++;
                }
                $performance[$key]['total_marks'] += $result->marks_obtained;
                $performance[$key]['max_marks_sum'] += $exam->maximum_marks;
            }
        }

        $final = [];
        foreach ($performance as $item) {
            if ($item['total_students'] > 0) {
                $avgMarks = round($item['total_marks'] / $item['total_students'], 2);
                $passPercentage = round(($item['passed'] / $item['total_students']) * 100, 2);
                $avgMax = round($item['max_marks_sum'] / $item['total_students'], 2);
                $avgPercentage = $avgMax > 0 ? round(($avgMarks / $avgMax) * 100, 2) : 0;
            } else {
                $avgMarks = 0;
                $passPercentage = 0;
                $avgPercentage = 0;
            }

            $final[] = [
                'class_section' => $item['class_section'],
                'exam_name' => $item['exam_name'],
                'total_students' => $item['total_students'],
                'passed' => $item['passed'],
                'failed' => $item['failed'],
                'average_marks' => $avgMarks,
                'pass_percentage' => $passPercentage . '%',
                'average_percentage' => $avgPercentage . '%',
            ];
        }

        return $final;
    }

    public function subjectPerformance(?int $academicYearId, ?int $examId, ?int $classSectionId): array
    {
        $query = Exam::with(['subject', 'classSection.schoolClass', 'classSection.section', 'results'])
            ->where('school_id', $this->schoolContext->id());

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        if ($examId) {
            $query->where('id', $examId);
        }
        if ($classSectionId) {
            $query->where('class_section_id', $classSectionId);
        }

        $exams = $query->get();
        $performance = [];

        foreach ($exams as $exam) {
            $subjectLabel = $exam->subject ? $exam->subject->name : 'Unknown';
            $classLabel = $exam->classSection ? $exam->classSection->schoolClass->name . ' - ' . $exam->classSection->section->name : 'Unknown';
            $key = $subjectLabel . '|' . $classLabel . '|' . $exam->exam_name;

            $results = $exam->results;
            if ($results->isEmpty()) continue;

            $totalStudents = $results->count();
            $passed = $results->where('status', 'pass')->count();
            $totalMarks = $results->sum('marks_obtained');
            $highestMarks = $results->max('marks_obtained');
            $lowestMarks = $results->min('marks_obtained');

            $performance[] = [
                'subject' => $subjectLabel,
                'class_section' => $classLabel,
                'exam_name' => $exam->exam_name,
                'total_students' => $totalStudents,
                'highest_marks' => $highestMarks,
                'lowest_marks' => $lowestMarks,
                'average_marks' => round($totalMarks / $totalStudents, 2),
                'pass_percentage' => round(($passed / $totalStudents) * 100, 2) . '%',
            ];
        }

        return $performance;
    }

    public function studentSummary(?int $studentId, ?int $academicYearId): array
    {
        if (!$studentId) {
            return [];
        }

        $query = ExamResult::with(['exam.subject', 'exam.academicYear'])
            ->where('student_id', $studentId)
            ->where('school_id', $this->schoolContext->id());

        if ($academicYearId) {
            $query->whereHas('exam', fn($q) => $q->where('academic_year_id', $academicYearId));
        }

        $results = $query->get();

        $summary = [];
        foreach ($results as $result) {
            $examName = $result->exam->exam_name;
            if (!isset($summary[$examName])) {
                $summary[$examName] = [
                    'exam_name' => $examName,
                    'academic_year' => $result->exam->academicYear?->name,
                    'total_marks_obtained' => 0,
                    'total_maximum_marks' => 0,
                    'subjects_passed' => 0,
                    'subjects_failed' => 0,
                    'subjects_count' => 0,
                ];
            }

            $summary[$examName]['total_marks_obtained'] += $result->marks_obtained;
            $summary[$examName]['total_maximum_marks'] += $result->exam->maximum_marks;
            $summary[$examName]['subjects_count']++;
            
            if ($result->status === 'pass') {
                $summary[$examName]['subjects_passed']++;
            } else {
                $summary[$examName]['subjects_failed']++;
            }
        }

        $final = [];
        foreach ($summary as $examName => $data) {
            $percentage = $data['total_maximum_marks'] > 0 ? round(($data['total_marks_obtained'] / $data['total_maximum_marks']) * 100, 2) : 0;
            $overallGrade = $this->calculateGrade($percentage);
            
            $final[] = [
                'exam_name' => $data['exam_name'],
                'academic_year' => $data['academic_year'],
                'total_obtained' => $data['total_marks_obtained'],
                'total_maximum' => $data['total_maximum_marks'],
                'percentage' => $percentage . '%',
                'overall_grade' => $overallGrade,
                'status' => $data['subjects_failed'] > 0 ? 'Fail' : 'Pass',
            ];
        }

        return array_values($final);
    }

    private function calculateGrade($percentage): string
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C';
        if ($percentage >= 40) return 'D';
        return 'F';
    }
}