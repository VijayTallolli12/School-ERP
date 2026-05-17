<?php

namespace App\Modules\Reports\Repositories;

use App\Modules\Parents\Models\Guardian;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ParentReportRepository implements ParentReportRepositoryInterface
{
    protected function getSchoolId()
    {
        return auth()->user()->school_id ?? null;
    }

    public function dashboardStats(): array
    {
        $schoolId = $this->getSchoolId();

        $totalParents = Guardian::where('school_id', $schoolId)->count();
        $activeParents = Guardian::where('school_id', $schoolId)->where('status', 'active')->count();

        $linkedStudentsCount = DB::table('parent_student')
            ->join('parents', 'parent_student.parent_id', '=', 'parents.id')
            ->where('parents.school_id', $schoolId)
            ->count();

        $mappedParents = Guardian::where('school_id', $schoolId)
            ->whereHas('students')
            ->count();

        return [
            'total_parents' => $totalParents,
            'active_parents' => $activeParents,
            'linked_students' => $linkedStudentsCount,
            'mapped_parents' => $mappedParents,
        ];
    }

    public function parentList(array $filters = []): array
    {
        $schoolId = $this->getSchoolId();

        $query = Guardian::with(['students.sessions.classSection.schoolClass', 'students.sessions.classSection.section'])
            ->where('school_id', $schoolId);

        $query
            ->when(Arr::get($filters, 'status'), fn ($q, $status) => $q->where('status', $status))
            ->when(Arr::get($filters, 'occupation'), fn ($q, $occupation) => $q->where('occupation', 'like', '%' . $occupation . '%'))
            ->when(Arr::get($filters, 'class_section_id'), function ($q, $classSectionId) {
                $q->whereHas('students.sessions', fn ($session) => $session->where('class_section_id', $classSectionId));
            });

        return $query->get()
            ->map(fn (Guardian $parent) => $this->parentListRow($parent))
            ->all();
    }

    public function mapping(array $filters = []): array
    {
        $schoolId = $this->getSchoolId();

        $query = Guardian::with(['students.sessions.classSection.schoolClass', 'students.sessions.classSection.section'])
            ->where('school_id', $schoolId)
            ->whereHas('students');

        $query
            ->when(Arr::get($filters, 'status'), fn ($q, $status) => $q->where('status', $status))
            ->when(Arr::get($filters, 'class_section_id'), function ($q, $classSectionId) {
                $q->whereHas('students.sessions', fn ($session) => $session->where('class_section_id', $classSectionId));
            });

        return $query->get()
            ->flatMap(function (Guardian $parent) use ($filters) {
                return $parent->students
                    ->filter(function ($student) use ($filters) {
                        $classSectionId = Arr::get($filters, 'class_section_id');

                        return empty($classSectionId)
                            || $student->sessions->contains('class_section_id', (int) $classSectionId);
                    })
                    ->map(fn ($student) => $this->mappingRow($parent, $student));
            })
            ->values()
            ->all();
    }

    public function activitySummary(array $filters = []): array
    {
        $schoolId = $this->getSchoolId();

        $query = Guardian::withCount('students')
            ->where('school_id', $schoolId);

        $query->when(Arr::get($filters, 'status'), fn ($q, $status) => $q->where('status', $status));

        $parents = $query->get();

        $result = [];
        foreach ($parents as $parent) {
            $userId = $parent->user_id;
            $notificationCount = $this->notificationCountForParent($parent, $filters);
            $attendanceAccess = $this->activityCount($userId, 'attendance', $filters);
            $feesAccess = $this->activityCount($userId, 'fee', $filters);
            $examAccess = $this->activityCount($userId, 'exam', $filters);

            $result[] = [
                'parent_id' => $parent->id,
                'parent_name' => $parent->full_name,
                'email' => $parent->email,
                'phone' => $parent->phone,
                'status' => $parent->status,
                'linked_students' => $parent->students_count,
                'notifications_count' => $notificationCount,
                'attendance_access' => $attendanceAccess,
                'fees_access' => $feesAccess,
                'exam_access' => $examAccess,
            ];
        }

        return $result;
    }

    private function parentListRow(Guardian $parent): array
    {
        return [
            'id' => $parent->id,
            'parent_name' => $parent->full_name,
            'email' => $parent->email,
            'phone' => $parent->phone,
            'occupation' => $parent->occupation,
            'status' => $parent->status,
            'linked_students' => $parent->students->count(),
            'classes' => $this->classesForParent($parent),
        ];
    }

    private function mappingRow(Guardian $parent, $student): array
    {
        return [
            'parent_id' => $parent->id,
            'parent_name' => $parent->full_name,
            'parent_email' => $parent->email,
            'parent_phone' => $parent->phone,
            'relationship' => $student->pivot->relationship ?? '',
            'is_primary' => (bool) ($student->pivot->is_primary ?? false),
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'admission_no' => $student->admission_no,
            'class_section' => $this->classSectionForStudent($student),
            'status' => $parent->status,
        ];
    }

    private function classSectionForStudent($student): string
    {
        $session = $student->sessions->firstWhere('status', 'active') ?? $student->sessions->first();

        if (! $session?->classSection) {
            return '-';
        }

        return trim(($session->classSection->schoolClass->name ?? '') . ' - ' . ($session->classSection->section->name ?? ''), ' -');
    }

    private function classesForParent(Guardian $parent): string
    {
        $classes = $parent->students
            ->map(fn ($student) => $this->classSectionForStudent($student))
            ->filter(fn ($classSection) => $classSection !== '-')
            ->unique()
            ->values();

        return $classes->isEmpty() ? '-' : $classes->implode(', ');
    }

    private function notificationCountForParent(Guardian $parent, array $filters = []): int
    {
        if (! Schema::hasTable('parent_notifications')) {
            return 0;
        }

        return DB::table('parent_notifications')
            ->where('school_id', $parent->school_id)
            ->whereNotNull('sent_at')
            ->when(Arr::get($filters, 'from_date'), fn ($q, $date) => $q->whereDate('sent_at', '>=', $date))
            ->when(Arr::get($filters, 'to_date'), fn ($q, $date) => $q->whereDate('sent_at', '<=', $date))
            ->where(function ($q) use ($parent) {
                $q->whereNull('target_parents')
                    ->orWhere('target_parents', 'like', '%"' . $parent->id . '"%')
                    ->orWhere('target_parents', 'like', '%[' . $parent->id . ']%')
                    ->orWhere('target_parents', 'like', '%,' . $parent->id . ',%');
            })
            ->count();
    }

    private function activityCount(?int $userId, string $module, array $filters = []): int
    {
        if (! $userId || ! Schema::hasTable('activity_log')) {
            return 0;
        }

        return DB::table('activity_log')
            ->where('causer_id', $userId)
            ->where(function ($q) use ($module) {
                $q->where('log_name', 'like', '%' . $module . '%')
                    ->orWhere('description', 'like', '%' . $module . '%');
            })
            ->when(Arr::get($filters, 'from_date'), fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when(Arr::get($filters, 'to_date'), fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->count();
    }
}
