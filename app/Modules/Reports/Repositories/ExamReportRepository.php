<?php

namespace App\Modules\Reports\Repositories;

use App\Core\Tenant\SchoolContext;
use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Models\ExamResult;
use App\Modules\Students\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExamReportRepository implements ExamReportRepositoryInterface
{
    public function __construct(private readonly SchoolContext $schoolContext) {}

    public function dashboardStats(): array
    {
        $schoolId = $this->schoolContext->id();

        $totalExams = Exam::where('school_id', $schoolId)->count();
        $publishedResults = Exam::where('school_id', $schoolId)->where('is_published', true)->count();
        $totalResults = ExamResult::where('school_id', $schoolId)->count();
        $passedResults = ExamResult::where('school_id', $schoolId)->where('status', 'pass')->count();

        $passPercentage = $totalResults > 0 ? round(($passedResults / $totalResults) * 100, 2) : 0;

        // Efficient subquery: count students with max marks per exam as toppers
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
        // Use SQL aggregation with GROUP BY instead of loading all results into PHP
        $schoolId = $this->schoolContext->id();

        $query = ExamResult::query()
            ->select([
                'exams.exam_name',
                'exams.maximum_marks',
                'class_section.id as class_section_id',
                'school_classes.name as class_name',
                'sections.name as section_name',
                DB::raw('COUNT(exam_results.id) as total_students'),
                DB::raw('SUM(CASE WHEN exam_results.status = \'pass\' THEN 1 ELSE 0 END) as passed'),
                DB::raw('SUM(CASE WHEN exam_results.status = \'fail\' THEN 1 ELSE 0 END) as failed'),
                DB::raw('SUM(exam_results.marks_obtained) as total_marks_obtained'),
                DB::raw('MAX(exams.maximum_marks) as max_marks_per_exam'),
            ])
            ->join('exams', 'exam_results.exam_id', '=', 'exams.id')
            ->join('class_section', 'exams.class_section_id', '=', 'class_section.id')
            ->join('school_classes', 'class_section.class_id', '=', 'school_classes.id')
            ->join('sections', 'class_section.section_id', '=', 'sections.id')
            ->where('exam_results.school_id', $schoolId);

        if ($academicYearId) {
            $query->where('exams.academic_year_id', $academicYearId);
        }
        if ($examId) {
            $query->where('exams.id', $examId);
        }

        $rows = $query
            ->groupBy('exams.id', 'exams.exam_name', 'exams.maximum_marks', 'class_section.id', 'school_classes.name', 'sections.name')
            ->orderBy('school_classes.name')
            ->get();

        $performance = [];
        foreach ($rows as $row) {
            $classLabel = $row->class_name . ' - ' . $row->section_name;
            $totalStudents = (int) $row->total_students;
            $passed = (int) $row->passed;
            $totalMarks = (float) $row->total_marks_obtained;
            $maxMarks = (float) $row->max_marks_per_exam;

            $avgMarks = $totalStudents > 0 ? round($totalMarks / $totalStudents, 2) : 0;
            $passPercentage = $totalStudents > 0 ? round(($passed / $totalStudents) * 100, 2) : 0;
            $avgPercentage = $maxMarks > 0 ? round(($avgMarks / $maxMarks) * 100, 2) : 0;

            $performance[] = [
                'class_section' => $classLabel,
                'exam_name' => $row->exam_name,
                'total_students' => $totalStudents,
                'passed' => $passed,
                'failed' => (int) $row->failed,
                'average_marks' => $avgMarks,
                'pass_percentage' => $passPercentage . '%',
                'average_percentage' => $avgPercentage . '%',
            ];
        }

        return $performance;
    }

    public function subjectPerformance(?int $academicYearId, ?int $examId, ?int $classSectionId): array
    {
        $schoolId = $this->schoolContext->id();

        $query = ExamResult::query()
            ->select([
                'subjects.name as subject_name',
                'exams.exam_name',
                'class_section.id as class_section_id',
                'school_classes.name as class_name',
                'sections.name as section_name',
                DB::raw('COUNT(exam_results.id) as total_students'),
                DB::raw('SUM(CASE WHEN exam_results.status = \'pass\' THEN 1 ELSE 0 END) as passed'),
                DB::raw('SUM(exam_results.marks_obtained) as total_marks_obtained'),
                DB::raw('MAX(exam_results.marks_obtained) as highest_marks'),
                DB::raw('MIN(exam_results.marks_obtained) as lowest_marks'),
            ])
            ->join('exams', 'exam_results.exam_id', '=', 'exams.id')
            ->join('subjects', 'exams.subject_id', '=', 'subjects.id')
            ->join('class_section', 'exams.class_section_id', '=', 'class_section.id')
            ->join('school_classes', 'class_section.class_id', '=', 'school_classes.id')
            ->join('sections', 'class_section.section_id', '=', 'sections.id')
            ->where('exam_results.school_id', $schoolId);

        if ($academicYearId) {
            $query->where('exams.academic_year_id', $academicYearId);
        }
        if ($examId) {
            $query->where('exams.id', $examId);
        }
        if ($classSectionId) {
            $query->where('exams.class_section_id', $classSectionId);
        }

        $rows = $query
            ->groupBy('subjects.id', 'subjects.name', 'exams.id', 'exams.exam_name', 'class_section.id', 'school_classes.name', 'sections.name')
            ->orderBy('subjects.name')
            ->get();

        $performance = [];
        foreach ($rows as $row) {
            $subjectLabel = $row->subject_name;
            $classLabel = $row->class_name . ' - ' . $row->section_name;
            $totalStudents = (int) $row->total_students;
            $passed = (int) $row->passed;

            $performance[] = [
                'subject' => $subjectLabel,
                'class_section' => $classLabel,
                'exam_name' => $row->exam_name,
                'total_students' => $totalStudents,
                'highest_marks' => (float) $row->highest_marks,
                'lowest_marks' => (float) $row->lowest_marks,
                'average_marks' => $totalStudents > 0 ? round((float) $row->total_marks_obtained / $totalStudents, 2) : 0,
                'pass_percentage' => $totalStudents > 0 ? round(($passed / $totalStudents) * 100, 2) . '%' : '0%',
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

    public function topPerformers(
        ?int $academicYearId,
        ?int $examId,
        ?int $classSectionId,
        ?int $subjectId,
        int $topN = 10
    ): array {
        $schoolId = $this->schoolContext->id();

        // Use SQL aggregation to compute student totals per exam set
        $query = ExamResult::query()
            ->select([
                'exam_results.student_id',
                DB::raw('SUM(exam_results.marks_obtained) as total_obtained'),
                DB::raw('SUM(exams.maximum_marks) as total_maximum'),
                DB::raw('GROUP_CONCAT(DISTINCT exams.exam_name SEPARATOR \', \') as exam_names'),
                'students.first_name',
                'students.last_name',
                'students.admission_no',
                'school_classes.name as class_name',
                'sections.name as section_name',
            ])
            ->join('exams', 'exam_results.exam_id', '=', 'exams.id')
            ->join('students', 'exam_results.student_id', '=', 'students.id')
            ->join('class_section', 'exams.class_section_id', '=', 'class_section.id')
            ->join('school_classes', 'class_section.class_id', '=', 'school_classes.id')
            ->join('sections', 'class_section.section_id', '=', 'sections.id')
            ->where('exam_results.school_id', $schoolId);

        if ($examId) {
            $query->where('exams.id', $examId);
        } else {
            if ($academicYearId) $query->where('exams.academic_year_id', $academicYearId);
            if ($classSectionId) $query->where('exams.class_section_id', $classSectionId);
            if ($subjectId) $query->where('exams.subject_id', $subjectId);
        }

        $studentRows = $query
            ->groupBy('exam_results.student_id', 'students.id', 'students.first_name', 'students.last_name', 'students.admission_no', 'school_classes.name', 'sections.name')
            ->get()
            ->keyBy('student_id');

        if ($studentRows->isEmpty()) {
            return [
                'ranked' => [],
                'summary' => ['highest_percentage' => 0, 'top_student' => 'N/A', 'class_average' => 0, 'students_evaluated' => 0],
                'grade_distribution' => [],
                'chartData' => [],
            ];
        }

        $classLabel = $studentRows->first()->class_name . ' - ' . $studentRows->first()->section_name;

        $ranked = [];
        foreach ($studentRows as $sid => $data) {
            $total = (float) $data->total_maximum;
            $obtained = (float) $data->total_obtained;
            $percentage = $total > 0 ? round(($obtained / $total) * 100, 2) : 0;

            $fullName = trim($data->first_name . ' ' . $data->last_name);

            $ranked[] = [
                'student_name' => $fullName ?: 'N/A',
                'admission_no' => $data->admission_no ?? '',
                'class_section' => $classLabel,
                'exam_name' => $data->exam_names ?? '',
                'total_marks' => $total,
                'obtained_marks' => $obtained,
                'percentage' => $percentage,
                'grade' => $this->calculateGrade($percentage),
                '_sort_admission' => $data->admission_no ?? '',
            ];
        }

        usort($ranked, function ($a, $b) {
            if ($b['percentage'] !== $a['percentage']) return $b['percentage'] <=> $a['percentage'];
            if ($b['obtained_marks'] !== $a['obtained_marks']) return $b['obtained_marks'] <=> $a['obtained_marks'];
            return strcmp($a['_sort_admission'], $b['_sort_admission']);
        });

        $ranked = array_slice($ranked, 0, $topN);

        foreach ($ranked as $i => &$row) {
            $row['rank'] = $i + 1;
            unset($row['_sort_admission']);
        }

        $percentages = array_column($ranked, 'percentage');
        $summary = [
            'highest_percentage' => !empty($percentages) ? max($percentages) : 0,
            'top_student' => !empty($ranked) ? $ranked[0]['student_name'] : 'N/A',
            'class_average' => !empty($percentages) ? round(array_sum($percentages) / count($percentages), 2) : 0,
            'students_evaluated' => count($ranked),
        ];

        $grade_distribution = [];
        foreach ($ranked as $row) {
            $g = $row['grade'];
            $grade_distribution[$g] = ($grade_distribution[$g] ?? 0) + 1;
        }

        $chartData = array_map(fn($r) => [
            'student' => $r['student_name'],
            'percentage' => $r['percentage'],
        ], $ranked);

        return compact('ranked', 'summary', 'grade_distribution', 'chartData');
    }

    public function passFailAnalysis(
        ?int $academicYearId,
        ?int $examId,
        ?int $classSectionId,
        ?int $subjectId,
        ?string $fromDate,
        ?string $toDate
    ): array {
        $schoolId = $this->schoolContext->id();

        // Base query with joins for efficient aggregation
        $baseQuery = ExamResult::query()
            ->join('exams', 'exam_results.exam_id', '=', 'exams.id')
            ->join('class_section', 'exams.class_section_id', '=', 'class_section.id')
            ->join('school_classes', 'class_section.class_id', '=', 'school_classes.id')
            ->join('sections', 'class_section.section_id', '=', 'sections.id')
            ->leftJoin('subjects', 'exams.subject_id', '=', 'subjects.id')
            ->where('exam_results.school_id', $schoolId);

        if ($examId) {
            $baseQuery->where('exams.id', $examId);
        } else {
            if ($academicYearId) $baseQuery->where('exams.academic_year_id', $academicYearId);
            if ($classSectionId) $baseQuery->where('exams.class_section_id', $classSectionId);
            if ($subjectId) $baseQuery->where('exams.subject_id', $subjectId);
        }

        if ($fromDate) {
            $baseQuery->whereDate('exams.exam_date', '>=', Carbon::parse($fromDate));
        }
        if ($toDate) {
            $baseQuery->whereDate('exams.exam_date', '<=', Carbon::parse($toDate));
        }

        // Overall stats (single query)
        $overallRow = (clone $baseQuery)->select([
            DB::raw('COUNT(*) as total_appeared'),
            DB::raw('SUM(CASE WHEN exam_results.status = \'pass\' THEN 1 ELSE 0 END) as total_passed'),
            DB::raw('SUM(CASE WHEN exam_results.status = \'fail\' THEN 1 ELSE 0 END) as total_failed'),
        ])->first();

        $totalAppeared = (int) ($overallRow->total_appeared ?? 0);
        $passed = (int) ($overallRow->total_passed ?? 0);
        $failed = (int) ($overallRow->total_failed ?? 0);
        $passPct = $totalAppeared > 0 ? round(($passed / $totalAppeared) * 100, 2) : 0;
        $failPct = $totalAppeared > 0 ? round(($failed / $totalAppeared) * 100, 2) : 0;

        $overall = [
            'total_appeared' => $totalAppeared,
            'total_passed' => $passed,
            'total_failed' => $failed,
            'pass_percentage' => $passPct,
            'fail_percentage' => $failPct,
        ];

        // Class-wise performance (GROUP BY)
        $classRows = (clone $baseQuery)
            ->select([
                'class_section.id as class_section_id',
                DB::raw("CONCAT(school_classes.name, ' - ', sections.name) as class_label"),
                DB::raw('COUNT(*) as appeared'),
                DB::raw('SUM(CASE WHEN exam_results.status = \'pass\' THEN 1 ELSE 0 END) as passed'),
                DB::raw('SUM(CASE WHEN exam_results.status = \'fail\' THEN 1 ELSE 0 END) as failed'),
                DB::raw('SUM(exam_results.marks_obtained) as total_marks_obtained'),
                DB::raw('SUM(exams.maximum_marks) as total_max_marks'),
            ])
            ->groupBy('class_section.id', 'school_classes.name', 'sections.name')
            ->get();

        $classPerformance = [];
        $classPcts = [];
        foreach ($classRows as $row) {
            $count = (int) $row->appeared;
            $passCount = (int) $row->passed;
            $pct = $count > 0 ? round(($passCount / $count) * 100, 2) : 0;
            $avgMarks = $count > 0 ? round((float) $row->total_marks_obtained / $count, 2) : 0;
            $avgPct = (float) $row->total_max_marks > 0 ? round(((float) $row->total_marks_obtained / (float) $row->total_max_marks) * 100, 2) : 0;

            $classPerformance[] = [
                'class_section' => $row->class_label,
                'appeared' => $count,
                'passed' => $passCount,
                'failed' => (int) $row->failed,
                'pass_pct' => $pct,
                'fail_pct' => round(100 - $pct, 2),
                'avg_marks' => $avgMarks,
                'avg_percentage' => $avgPct,
            ];
            $classPcts[$row->class_label] = $pct;
        }

        arsort($classPcts);
        $bestClass = array_key_first($classPcts);
        $lowestClass = !empty($classPcts) ? array_key_last($classPcts) : '';
        $avgPctOverall = !empty($classPcts) ? round(array_sum($classPcts) / count($classPcts), 2) : 0;

        // Subject-wise performance (GROUP BY)
        $subjectRows = (clone $baseQuery)
            ->select([
                'subjects.id as subject_id',
                DB::raw('COALESCE(subjects.name, \'Unknown\') as subject_name'),
                DB::raw('COUNT(*) as appeared'),
                DB::raw('SUM(CASE WHEN exam_results.status = \'pass\' THEN 1 ELSE 0 END) as passed'),
                DB::raw('SUM(CASE WHEN exam_results.status = \'fail\' THEN 1 ELSE 0 END) as failed'),
            ])
            ->groupBy('subjects.id', 'subjects.name')
            ->get();

        $subjectAnalysis = [];
        $subjectPcts = [];
        foreach ($subjectRows as $row) {
            $count = (int) $row->appeared;
            $passCount = (int) $row->passed;
            $pct = $count > 0 ? round(($passCount / $count) * 100, 2) : 0;

            $subjectAnalysis[] = [
                'subject' => $row->subject_name,
                'appeared' => $count,
                'passed' => $passCount,
                'failed' => (int) $row->failed,
                'pass_pct' => $pct,
                'fail_pct' => round(100 - $pct, 2),
            ];
            $subjectPcts[$row->subject_name] = $pct;
        }

        $highestSubject = !empty($subjectPcts) ? array_search(max($subjectPcts), $subjectPcts) : '';
        $lowestSubject = !empty($subjectPcts) ? array_search(min($subjectPcts), $subjectPcts) : '';

        // Individual student results (paginated, limited to 5000 for UI display)
        $studentQuery = ExamResult::with([
            'exam.classSection.schoolClass', 'exam.classSection.section',
            'exam.subject', 'student',
        ])->where('school_id', $schoolId);

        if ($examId) {
            $studentQuery->where('exam_id', $examId);
        } else {
            $studentQuery->whereHas('exam', function ($q) use ($academicYearId, $classSectionId, $subjectId) {
                if ($academicYearId) $q->where('academic_year_id', $academicYearId);
                if ($classSectionId) $q->where('class_section_id', $classSectionId);
                if ($subjectId) $q->where('subject_id', $subjectId);
            });
        }

        if ($fromDate) {
            $studentQuery->whereHas('exam', fn($q) => $q->whereDate('exam_date', '>=', Carbon::parse($fromDate)));
        }
        if ($toDate) {
            $studentQuery->whereHas('exam', fn($q) => $q->whereDate('exam_date', '<=', Carbon::parse($toDate)));
        }

        $studentAnalysis = $studentQuery->limit(5000)->get()->map(fn($r) => [
            'student_name' => $r->student?->full_name ?? 'N/A',
            'admission_no' => $r->student?->admission_no ?? '',
            'class_section' => $r->exam->classSection
                ? $r->exam->classSection->schoolClass->name . ' - ' . $r->exam->classSection->section->name
                : '',
            'exam_name' => $r->exam->exam_name,
            'percentage' => $r->exam->maximum_marks > 0
                ? round(($r->marks_obtained / $r->exam->maximum_marks) * 100, 2)
                : 0,
            'result' => $r->status === 'pass' ? 'Pass' : 'Fail',
        ])->values()->toArray();

        $chartData = [
            'pass_vs_fail' => ['passed' => $passed, 'failed' => $failed],
            'class_pass_pct' => array_map(fn($c) => ['label' => $c['class_section'], 'value' => $c['pass_pct']], $classPerformance),
            'subject_pass_pct' => array_map(fn($s) => ['label' => $s['subject'], 'value' => $s['pass_pct']], $subjectAnalysis),
        ];

        return compact(
            'overall', 'classPerformance', 'subjectAnalysis', 'studentAnalysis',
            'bestClass', 'lowestClass', 'avgPctOverall',
            'highestSubject', 'lowestSubject',
            'chartData'
        );
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
