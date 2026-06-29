<?php

namespace App\Modules\Reports\ViewComposers;

use App\Core\Tenant\SchoolContext;
use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Models\ExamResult;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportDashboardComposer
{
    public function __construct(
        private readonly SchoolContext $schoolContext,
    ) {}

    public function compose(View $view): void
    {
        $name = $view->name();

        if (str_contains($name, 'teachers.index')) {
            $this->composeTeacher($view);
        } elseif (str_contains($name, 'parents.index')) {
            $this->composeParent($view);
        } elseif (str_contains($name, 'exams.index')) {
            $this->composeExam($view);
        }
    }

    private function composeTeacher(View $view): void
    {
        $schoolId = $this->schoolContext->id();

        $subjectData = DB::table('teacher_subject')
            ->join('subjects', 'teacher_subject.subject_id', '=', 'subjects.id')
            ->join('teachers', 'teacher_subject.teacher_id', '=', 'teachers.id')
            ->where('teachers.school_id', $schoolId)
            ->select('subjects.name', DB::raw('COUNT(DISTINCT teacher_subject.teacher_id) as count'))
            ->groupBy('subjects.id', 'subjects.name')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }

        $attendanceData = TeacherAttendance::join('teachers', 'teacher_attendances.teacher_id', '=', 'teachers.id')
            ->where('teachers.school_id', $schoolId)
            ->where('teacher_attendances.attendance_date', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(teacher_attendances.attendance_date, '%Y-%m') as month")
            ->selectRaw("SUM(CASE WHEN teacher_attendances.status = 'present' THEN 1 ELSE 0 END) as present")
            ->selectRaw("SUM(CASE WHEN teacher_attendances.status = 'absent' THEN 1 ELSE 0 END) as absent")
            ->selectRaw("SUM(CASE WHEN teacher_attendances.status = 'late' THEN 1 ELSE 0 END) as late")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $trendLabels = [];
        $trendPresent = [];
        $trendAbsent = [];
        foreach ($months as $m) {
            $trendLabels[] = \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M');
            $row = $attendanceData->get($m);
            $trendPresent[] = (int) ($row->present ?? 0);
            $trendAbsent[] = (int) ($row->absent ?? 0);
        }

        $teacherAttendanceCount = DB::table('teacher_attendances')
            ->join('teachers', 'teacher_attendances.teacher_id', '=', 'teachers.id')
            ->where('teachers.school_id', $schoolId)
            ->count();

        $subjectAllocationCount = DB::table('teacher_subject')
            ->join('teachers', 'teacher_subject.teacher_id', '=', 'teachers.id')
            ->where('teachers.school_id', $schoolId)
            ->count();

        $classTeacherCount = DB::table('teacher_class_section')
            ->join('teachers', 'teacher_class_section.teacher_id', '=', 'teachers.id')
            ->where('teachers.school_id', $schoolId)
            ->where('teacher_class_section.is_class_teacher', 1)
            ->count();

        $view->with('chartData', [
            'subjectLabels' => $subjectData->pluck('name')->toArray(),
            'subjectCounts' => $subjectData->pluck('count')->toArray(),
            'trendLabels' => $trendLabels,
            'trendPresent' => $trendPresent,
            'trendAbsent' => $trendAbsent,
        ]);

        $view->with('reportStats', [
            'teacher_list' => DB::table('teachers')->where('school_id', $schoolId)->count(),
            'teacher_attendance' => $teacherAttendanceCount,
            'subject_allocation' => $subjectAllocationCount,
            'class_teacher' => $classTeacherCount,
        ]);
    }

    private function composeParent(View $view): void
    {
        $schoolId = $this->schoolContext->id();

        $studentCounts = DB::table('parent_student')
            ->join('parents', 'parent_student.parent_id', '=', 'parents.id')
            ->where('parents.school_id', $schoolId)
            ->select('parent_id', DB::raw('COUNT(*) as student_count'))
            ->groupBy('parent_id')
            ->get()
            ->pluck('student_count');

        $engagementBuckets = [
            '1 Student' => 0,
            '2 Students' => 0,
            '3 Students' => 0,
            '4+ Students' => 0,
        ];
        foreach ($studentCounts as $count) {
            if ($count >= 4) {
                $engagementBuckets['4+ Students']++;
            } elseif ($count === 3) {
                $engagementBuckets['3 Students']++;
            } elseif ($count === 2) {
                $engagementBuckets['2 Students']++;
            } else {
                $engagementBuckets['1 Student']++;
            }
        }

        $totalParents = Guardian::where('school_id', $schoolId)->count();
        $activeParents = Guardian::where('school_id', $schoolId)->where('status', 'active')->count();
        $inactiveParents = $totalParents - $activeParents;

        $mappedParentCount = DB::table('parent_student')
            ->join('parents', 'parent_student.parent_id', '=', 'parents.id')
            ->where('parents.school_id', $schoolId)
            ->distinct('parent_student.parent_id')
            ->count('parent_student.parent_id');

        $view->with('chartData', [
            'engagementLabels' => array_keys($engagementBuckets),
            'engagementCounts' => array_values($engagementBuckets),
            'statusLabels' => ['Active', 'Inactive'],
            'statusCounts' => [$activeParents, $inactiveParents],
        ]);

        $view->with('reportStats', [
            'parent_list' => $totalParents,
            'parent_mapping' => $mappedParentCount,
            'parent_activity' => 0,
        ]);
    }

    private function composeExam(View $view): void
    {
        $schoolId = $this->schoolContext->id();

        $exams = Exam::where('school_id', $schoolId)->get(['id', 'exam_name', 'is_published']);
        $published = $exams->where('is_published', true)->count();
        $unpublished = $exams->where('is_published', false)->count();

        $examPassData = ExamResult::where('exam_results.school_id', $schoolId)
            ->join('exams', 'exam_results.exam_id', '=', 'exams.id')
            ->select('exams.exam_name', 'exams.id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN exam_results.status = 'pass' THEN 1 ELSE 0 END) as passed")
            ->groupBy('exams.id', 'exams.exam_name')
            ->orderByDesc('exams.id')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'label' => $r->exam_name,
                'pass_pct' => $r->total > 0 ? round(($r->passed / $r->total) * 100) : 0,
            ]);

        $examResultCount = DB::table('exam_results')
            ->where('school_id', $schoolId)
            ->count();

        $view->with('chartData', [
            'passLabels' => $examPassData->pluck('label')->toArray(),
            'passValues' => $examPassData->pluck('pass_pct')->toArray(),
            'publishedCount' => $published,
            'unpublishedCount' => $unpublished,
        ]);

        $view->with('reportStats', [
            'exam_results' => $examResultCount,
            'class_performance' => $exams->count(),
            'subject_performance' => DB::table('exams')->where('school_id', $schoolId)->count(DB::raw('DISTINCT subject_id')),
            'student_summary' => $examResultCount,
            'top_performers' => DB::table('exam_results')->where('school_id', $schoolId)->where('status', 'pass')->count(),
            'pass_fail' => $examResultCount,
        ]);
    }
}
