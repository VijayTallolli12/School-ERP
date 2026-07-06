<?php

namespace App\Modules\AiAssistant\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use App\Modules\Exams\Models\Exam;
use App\Modules\Fees\Models\FeeCategory;
use App\Modules\Leave\Models\LeaveType;
use App\Modules\Payroll\Models\PayrollDepartment;
use App\Modules\Transport\Models\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ParameterResolver
{
    private const CACHE_TTL = 600; // 10 minutes

    private const ALIASES = [
        'class' => [
            'math' => ['mathematics'],
            'mathematics' => ['math'],
            'science' => ['sc'],
            'sc' => ['science'],
            'english' => ['eng'],
            'eng' => ['english'],
            'social studies' => ['sst', 'social science'],
            'sst' => ['social studies', 'social science'],
            'computer science' => ['cs', 'computer'],
            'cs' => ['computer science', 'computer'],
        ],
        'subject' => [
            'math' => ['mathematics'],
            'mathematics' => ['math'],
            'science' => ['sc'],
            'sc' => ['science'],
            'english' => ['eng'],
            'eng' => ['english'],
            'social studies' => ['sst', 'social science'],
            'sst' => ['social studies', 'social science'],
            'computer science' => ['cs', 'computer'],
            'cs' => ['computer science', 'computer'],
        ],
        'route' => [],
        'exam' => [],
        'department' => [],
        'fee_category' => [
            'tuition' => ['tuition fees', 'tuition fee'],
            'transport' => ['transport fees', 'transport fee', 'bus'],
            'hostel' => ['hostel fees', 'hostel fee'],
            'exam' => ['exam fees', 'exam fee'],
            'miscellaneous' => ['misc', 'misc fees'],
        ],
        'leave_type' => [
            'sick' => ['sick leave'],
            'casual' => ['casual leave'],
            'annual' => ['annual leave', 'earned leave'],
            'earned' => ['annual leave', 'earned leave'],
        ],
    ];

    public function resolve(array $parameters): array
    {
        $startTime = microtime(true);

        $resolved = $parameters;

        if (!empty($parameters['class']) && empty($parameters['class_section_id'])) {
            $resolved['class_section_id'] = $this->resolveClassSection($parameters['class']);
        }

        if (!empty($parameters['section']) && empty($parameters['class_section_id'])) {
            $resolved['class_section_id'] = $this->resolveClassSectionBySection($parameters['section']);
        }

        if (!empty($parameters['subject']) && empty($parameters['subject_id'])) {
            $resolved['subject_id'] = $this->resolveSubject($parameters['subject']);
        }

        if (!empty($parameters['route']) && empty($parameters['route_id'])) {
            $resolved['route_id'] = $this->resolveRoute($parameters['route']);
        }

        if (!empty($parameters['exam']) && empty($parameters['exam_id'])) {
            $resolved['exam_id'] = $this->resolveExam($parameters['exam']);
        }

        if (!empty($parameters['department']) && empty($parameters['department_id'])) {
            $resolved['department_id'] = $this->resolveDepartment($parameters['department']);
        }

        if (!empty($parameters['fee_category']) && empty($parameters['fee_category_id'])) {
            $resolved['fee_category_id'] = $this->resolveFeeCategory($parameters['fee_category']);
        }

        if (!empty($parameters['leave_type']) && empty($parameters['leave_type_id'])) {
            $resolved['leave_type_id'] = $this->resolveLeaveType($parameters['leave_type']);
        }

        $elapsed = round((microtime(true) - $startTime) * 1000, 1);

        $this->logDebug('Parameters resolved', [
            'input' => $parameters,
            'output' => $resolved,
            'duration_ms' => $elapsed,
        ]);

        return $resolved;
    }

    private function resolveClassSection(string $className): ?int
    {
        $schoolId = app(SchoolContext::class)->id();

        if (!$schoolId) {
            return null;
        }

        $cacheKey = "ai_class_sections_{$schoolId}";

        $classSections = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($schoolId) {
            return ClassSection::query()
                ->where('school_id', $schoolId)
                ->where('status', 'active')
                ->with(['schoolClass', 'section'])
                ->get()
                ->map(fn ($cs) => [
                    'id' => $cs->id,
                    'class_name' => $cs->schoolClass->name ?? '',
                    'section_name' => $cs->section->name ?? '',
                    'section_code' => $cs->section->code ?? '',
                ])
                ->toArray();
        });

        $normalizedInput = $this->normalize($className);

        foreach ($classSections as $cs) {
            $classMatch = $this->matchesWithAliases($normalizedInput, $cs['class_name'], 'class');

            if ($classMatch && !$this->hasSectionInInput($normalizedInput)) {
                $this->logDebug('Class resolved', [
                    'input' => $className,
                    'class_section_id' => $cs['id'],
                    'class' => $cs['class_name'],
                    'section' => $cs['section_name'],
                ]);
                return $cs['id'];
            }

            if ($classMatch && $this->hasSectionInInput($normalizedInput)) {
                $sectionInput = $this->extractSection($normalizedInput);
                $sectionName = $this->normalize($cs['section_name']);
                $sectionCode = strtolower($cs['section_code']);
                if ($sectionInput === $sectionName || $sectionInput === $sectionCode || str_ends_with($sectionName, $sectionInput)) {
                    $this->logDebug('Class+Section resolved', [
                        'input' => $className,
                        'class_section_id' => $cs['id'],
                        'class' => $cs['class_name'],
                        'section' => $cs['section_name'],
                    ]);
                    return $cs['id'];
                }
            }
        }

        $this->logDebug('Class not resolved', ['input' => $className]);
        return null;
    }

    private function resolveClassSectionBySection(string $sectionName): ?int
    {
        $schoolId = app(SchoolContext::class)->id();

        if (!$schoolId) {
            return null;
        }

        $cacheKey = "ai_class_sections_{$schoolId}";

        $classSections = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($schoolId) {
            return ClassSection::query()
                ->where('school_id', $schoolId)
                ->where('status', 'active')
                ->with(['schoolClass', 'section'])
                ->get()
                ->map(fn ($cs) => [
                    'id' => $cs->id,
                    'class_name' => $cs->schoolClass->name ?? '',
                    'section_name' => $cs->section->name ?? '',
                    'section_code' => $cs->section->code ?? '',
                ])
                ->toArray();
        });

        $normalizedInput = $this->normalize($sectionName);

        foreach ($classSections as $cs) {
            if ($this->matchesWithAliases($normalizedInput, $cs['section_name'], 'class')) {
                $this->logDebug('Section resolved', [
                    'input' => $sectionName,
                    'class_section_id' => $cs['id'],
                    'class' => $cs['class_name'],
                    'section' => $cs['section_name'],
                ]);
                return $cs['id'];
            }
        }

        $this->logDebug('Section not resolved', ['input' => $sectionName]);
        return null;
    }

    private function resolveSubject(string $subjectName): ?int
    {
        $schoolId = app(SchoolContext::class)->id();

        if (!$schoolId) {
            return null;
        }

        $cacheKey = "ai_subjects_{$schoolId}";

        $subjects = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($schoolId) {
            return Subject::query()
                ->where('school_id', $schoolId)
                ->where('status', 'active')
                ->get()
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])
                ->toArray();
        });

        $normalizedInput = $this->normalize($subjectName);

        foreach ($subjects as $subject) {
            if ($this->matchesWithAliases($normalizedInput, $subject['name'], 'subject')) {
                $this->logDebug('Subject resolved', [
                    'input' => $subjectName,
                    'subject_id' => $subject['id'],
                    'name' => $subject['name'],
                ]);
                return $subject['id'];
            }
        }

        $this->logDebug('Subject not resolved', ['input' => $subjectName]);
        return null;
    }

    private function resolveRoute(string $routeName): ?int
    {
        $schoolId = app(SchoolContext::class)->id();

        if (!$schoolId) {
            return null;
        }

        $cacheKey = "ai_routes_{$schoolId}";

        $routes = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($schoolId) {
            return Route::query()
                ->where('school_id', $schoolId)
                ->where('status', 'active')
                ->get()
                ->map(fn ($r) => ['id' => $r->id, 'route_name' => $r->route_name])
                ->toArray();
        });

        $normalizedInput = $this->normalize($routeName);

        foreach ($routes as $route) {
            if ($this->matchesWithAliases($normalizedInput, $route['route_name'], 'route')) {
                $this->logDebug('Route resolved', [
                    'input' => $routeName,
                    'route_id' => $route['id'],
                    'name' => $route['route_name'],
                ]);
                return $route['id'];
            }
        }

        $this->logDebug('Route not resolved', ['input' => $routeName]);
        return null;
    }

    private function resolveExam(string $examName): ?int
    {
        $schoolId = app(SchoolContext::class)->id();

        if (!$schoolId) {
            return null;
        }

        $cacheKey = "ai_exams_{$schoolId}";

        $exams = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($schoolId) {
            return Exam::query()
                ->where('school_id', $schoolId)
                ->where('status', '!=', 'canceled')
                ->get()
                ->map(fn ($e) => ['id' => $e->id, 'exam_name' => $e->exam_name])
                ->toArray();
        });

        $normalizedInput = $this->normalize($examName);

        foreach ($exams as $exam) {
            if ($this->matchesWithAliases($normalizedInput, $exam['exam_name'], 'exam')) {
                $this->logDebug('Exam resolved', [
                    'input' => $examName,
                    'exam_id' => $exam['id'],
                    'name' => $exam['exam_name'],
                ]);
                return $exam['id'];
            }
        }

        $this->logDebug('Exam not resolved', ['input' => $examName]);
        return null;
    }

    private function resolveDepartment(string $departmentName): ?int
    {
        $schoolId = app(SchoolContext::class)->id();

        if (!$schoolId) {
            return null;
        }

        $cacheKey = "ai_departments_{$schoolId}";

        $departments = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($schoolId) {
            return PayrollDepartment::query()
                ->where('school_id', $schoolId)
                ->where('status', 'active')
                ->get()
                ->map(fn ($d) => ['id' => $d->id, 'name' => $d->name])
                ->toArray();
        });

        $normalizedInput = $this->normalize($departmentName);

        foreach ($departments as $department) {
            if ($this->matchesWithAliases($normalizedInput, $department['name'], 'department')) {
                $this->logDebug('Department resolved', [
                    'input' => $departmentName,
                    'department_id' => $department['id'],
                    'name' => $department['name'],
                ]);
                return $department['id'];
            }
        }

        $this->logDebug('Department not resolved', ['input' => $departmentName]);
        return null;
    }

    private function resolveFeeCategory(string $categoryName): ?int
    {
        $schoolId = app(SchoolContext::class)->id();

        if (!$schoolId) {
            return null;
        }

        $cacheKey = "ai_fee_categories_{$schoolId}";

        $categories = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($schoolId) {
            return FeeCategory::query()
                ->where('school_id', $schoolId)
                ->get()
                ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
                ->toArray();
        });

        $normalizedInput = $this->normalize($categoryName);

        foreach ($categories as $category) {
            if ($this->matchesWithAliases($normalizedInput, $category['name'], 'fee_category')) {
                $this->logDebug('Fee category resolved', [
                    'input' => $categoryName,
                    'fee_category_id' => $category['id'],
                    'name' => $category['name'],
                ]);
                return $category['id'];
            }
        }

        $this->logDebug('Fee category not resolved', ['input' => $categoryName]);
        return null;
    }

    private function resolveLeaveType(string $leaveTypeName): ?int
    {
        $schoolId = app(SchoolContext::class)->id();

        if (!$schoolId) {
            return null;
        }

        $cacheKey = "ai_leave_types_{$schoolId}";

        $leaveTypes = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($schoolId) {
            return LeaveType::query()
                ->where('school_id', $schoolId)
                ->where('is_active', true)
                ->get()
                ->map(fn ($lt) => ['id' => $lt->id, 'name' => $lt->name])
                ->toArray();
        });

        $normalizedInput = $this->normalize($leaveTypeName);

        foreach ($leaveTypes as $leaveType) {
            if ($this->matchesWithAliases($normalizedInput, $leaveType['name'], 'leave_type')) {
                $this->logDebug('Leave type resolved', [
                    'input' => $leaveTypeName,
                    'leave_type_id' => $leaveType['id'],
                    'name' => $leaveType['name'],
                ]);
                return $leaveType['id'];
            }
        }

        $this->logDebug('Leave type not resolved', ['input' => $leaveTypeName]);
        return null;
    }

    private function normalize(string $value): string
    {
        $normalized = mb_strtolower(trim($value));
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return $normalized;
    }

    private function matchesWithAliases(string $input, string $dbValue, string $type): bool
    {
        $normalizedDb = $this->normalize($dbValue);

        if ($input === $normalizedDb) {
            return true;
        }

        if (preg_match('/^' . preg_quote($input, '/') . '\b/', $normalizedDb)) {
            return true;
        }

        if (preg_match('/\b' . preg_quote($input, '/') . '$/', $normalizedDb)) {
            return true;
        }

        $aliases = self::ALIASES[$type] ?? [];
        $inputAliases = $aliases[$input] ?? [];
        $dbAliases = $aliases[$normalizedDb] ?? [];

        foreach ($inputAliases as $alias) {
            if ($alias === $normalizedDb || preg_match('/^' . preg_quote($alias, '/') . '\b/', $normalizedDb)) {
                return true;
            }
        }

        foreach ($dbAliases as $alias) {
            if ($alias === $input || preg_match('/\b' . preg_quote($alias, '/') . '$/', $normalizedDb)) {
                return true;
            }
        }

        return false;
    }

    private function hasSectionInInput(string $input): bool
    {
        return preg_match('/\b(section|sec|div|division)\b/i', $input) === 1
            || preg_match('/\b[A-Da-d]\b/', $input) === 1;
    }

    private function extractSection(string $input): string
    {
        if (preg_match('/\b(section|sec|div|division)\s*([A-Da-d])\b/i', $input, $matches)) {
            return strtolower($matches[2]);
        }

        if (preg_match('/\b([A-Da-d])\b/', $input, $matches)) {
            return strtolower($matches[1]);
        }

        return '';
    }

    public function clearCache(): void
    {
        $schoolId = app(SchoolContext::class)->id();

        if (!$schoolId) {
            return;
        }

        Cache::forget("ai_class_sections_{$schoolId}");
        Cache::forget("ai_subjects_{$schoolId}");
        Cache::forget("ai_routes_{$schoolId}");
        Cache::forget("ai_exams_{$schoolId}");
        Cache::forget("ai_departments_{$schoolId}");
        Cache::forget("ai_fee_categories_{$schoolId}");
        Cache::forget("ai_leave_types_{$schoolId}");
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (!app()->environment('local', 'development')) {
            return;
        }

        Log::channel('daily')->debug("[AI Params] {$message}", $context);
    }
}
