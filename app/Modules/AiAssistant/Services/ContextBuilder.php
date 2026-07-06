<?php

namespace App\Modules\AiAssistant\Services;

use App\Models\AcademicYear;
use App\Models\School;
use App\Core\Tenant\SchoolContext;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use App\Modules\Exams\Models\Exam;
use App\Modules\Fees\Models\FeeCategory;
use App\Modules\Leave\Models\LeaveType;
use App\Modules\Payroll\Models\PayrollDepartment;
use App\Modules\Transport\Models\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ContextBuilder
{
    private const CACHE_TTL = 300; // 5 minutes

    private const MAX_ITEMS = [
        'classes' => 10,
        'sections' => 10,
        'routes' => 10,
        'subjects' => 10,
        'departments' => 10,
        'exams' => 10,
        'fee_categories' => 10,
        'leave_types' => 10,
    ];

    public function buildContext(): string
    {
        $startTime = microtime(true);

        $schoolId = app(SchoolContext::class)->id();

        if (!$schoolId) {
            return '';
        }

        $cacheKey = "ai_context_{$schoolId}";

        $context = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($schoolId) {
            return $this->fetchContext($schoolId);
        });

        $elapsed = round((microtime(true) - $startTime) * 1000, 1);

        $this->logDebug('Context built', [
            'school_id' => $schoolId,
            'context_length' => strlen($context),
            'tokens_estimated' => $this->estimateTokens($context),
            'generation_time_ms' => $elapsed,
        ]);

        return $context;
    }

    public function clearCache(): void
    {
        $schoolId = app(SchoolContext::class)->id();

        if ($schoolId) {
            Cache::forget("ai_context_{$schoolId}");
        }
    }

    private function fetchContext(int $schoolId): string
    {
        $sections = [];

        $sections[] = $this->buildDateContext();
        $sections[] = $this->buildAcademicYearContext($schoolId);
        $sections[] = $this->buildUserContext();
        $sections[] = $this->buildClassContext($schoolId);
        $sections[] = $this->buildRouteContext($schoolId);
        $sections[] = $this->buildExamContext($schoolId);
        $sections[] = $this->buildSubjectContext($schoolId);
        $sections[] = $this->buildFeeCategoryContext($schoolId);
        $sections[] = $this->buildDepartmentContext($schoolId);
        $sections[] = $this->buildLeaveTypeContext($schoolId);

        $sections = array_filter($sections, fn ($s) => $s !== '');

        return implode("\n", $sections);
    }

    private function buildDateContext(): string
    {
        $now = now();

        return <<<CTX
Current Date: {$now->format('Y-m-d')}
Current Month: {$now->format('F')}
Current Year: {$now->format('Y')}
CTX;
    }

    private function buildAcademicYearContext(int $schoolId): string
    {
        $academicYear = AcademicYear::query()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        if (!$academicYear) {
            return '';
        }

        $name = $academicYear->name;
        $starts = $academicYear->starts_on?->format('Y-m-d') ?? 'N/A';
        $ends = $academicYear->ends_on?->format('Y-m-d') ?? 'N/A';

        return <<<CTX
Academic Year: {$name} ({$starts} to {$ends})
CTX;
    }

    private function buildUserContext(): string
    {
        $user = Auth::user();

        if (!$user) {
            return '';
        }

        $role = 'User';
        if ($user->isSuperAdmin()) {
            $role = 'Super Admin';
        } elseif ($user->hasRole('admin')) {
            $role = 'Admin';
        } elseif ($user->hasRole('teacher')) {
            $role = 'Teacher';
        } elseif ($user->hasRole('parent')) {
            $role = 'Parent';
        } elseif ($user->hasRole('student')) {
            $role = 'Student';
        }

        return <<<CTX
User Role: {$role}
CTX;
    }

    private function buildClassContext(int $schoolId): string
    {
        $classSections = ClassSection::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->with(['schoolClass', 'section'])
            ->limit(self::MAX_ITEMS['classes'])
            ->get();

        if ($classSections->isEmpty()) {
            return '';
        }

        $classNames = $classSections
            ->pluck('schoolClass.name')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $sectionNames = $classSections
            ->pluck('section.name')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $classes = implode(', ', array_slice($classNames, 0, self::MAX_ITEMS['classes']));
        $sections = implode(', ', array_slice($sectionNames, 0, self::MAX_ITEMS['sections']));

        return <<<CTX
Available Classes: {$classes}
Available Sections: {$sections}
CTX;
    }

    private function buildRouteContext(int $schoolId): string
    {
        $routes = Route::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->limit(self::MAX_ITEMS['routes'])
            ->get();

        if ($routes->isEmpty()) {
            return '';
        }

        $routeNames = $routes->pluck('route_name')->toArray();
        $routeList = implode(', ', $routeNames);

        return <<<CTX
Transport Routes: {$routeList}
CTX;
    }

    private function buildExamContext(int $schoolId): string
    {
        $exams = Exam::query()
            ->where('school_id', $schoolId)
            ->where('status', '!=', 'canceled')
            ->limit(self::MAX_ITEMS['exams'])
            ->get();

        if ($exams->isEmpty()) {
            return '';
        }

        $examNames = $exams->pluck('exam_name')->unique()->toArray();
        $examList = implode(', ', array_slice($examNames, 0, self::MAX_ITEMS['exams']));

        return <<<CTX
Exam Names: {$examList}
CTX;
    }

    private function buildSubjectContext(int $schoolId): string
    {
        $subjects = Subject::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->limit(self::MAX_ITEMS['subjects'])
            ->get();

        if ($subjects->isEmpty()) {
            return '';
        }

        $subjectNames = $subjects->pluck('name')->toArray();
        $subjectList = implode(', ', $subjectNames);

        return <<<CTX
Subjects: {$subjectList}
CTX;
    }

    private function buildFeeCategoryContext(int $schoolId): string
    {
        $categories = FeeCategory::query()
            ->where('school_id', $schoolId)
            ->limit(self::MAX_ITEMS['fee_categories'])
            ->get();

        if ($categories->isEmpty()) {
            return '';
        }

        $categoryNames = $categories->pluck('name')->toArray();
        $categoryList = implode(', ', $categoryNames);

        return <<<CTX
Fee Categories: {$categoryList}
CTX;
    }

    private function buildDepartmentContext(int $schoolId): string
    {
        $departments = PayrollDepartment::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->limit(self::MAX_ITEMS['departments'])
            ->get();

        if ($departments->isEmpty()) {
            return '';
        }

        $departmentNames = $departments->pluck('name')->toArray();
        $departmentList = implode(', ', $departmentNames);

        return <<<CTX
Departments: {$departmentList}
CTX;
    }

    private function buildLeaveTypeContext(int $schoolId): string
    {
        $leaveTypes = LeaveType::query()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->limit(self::MAX_ITEMS['leave_types'])
            ->get();

        if ($leaveTypes->isEmpty()) {
            return '';
        }

        $leaveTypeNames = $leaveTypes->pluck('name')->toArray();
        $leaveTypeList = implode(', ', $leaveTypeNames);

        return <<<CTX
Leave Types: {$leaveTypeList}
CTX;
    }

    public function estimateTokens(string $text): int
    {
        return (int) ceil(mb_strlen($text) / 4);
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (!app()->environment('local', 'development')) {
            return;
        }

        Log::channel('daily')->debug("[AI Context] {$message}", $context);
    }
}
