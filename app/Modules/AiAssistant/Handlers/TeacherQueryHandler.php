<?php

namespace App\Modules\AiAssistant\Handlers;

use App\Core\Tenant\SchoolContext;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherAttendance;
use App\Modules\Teachers\Models\TeacherLeave;
use Illuminate\Support\Carbon;

class TeacherQueryHandler
{
    private const MAX_RESULTS = 50;

    public function __construct(
        private readonly SchoolContext $schoolContext,
    ) {}

    public function totalTeachers(array $parameters): array
    {
        $count = Teacher::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('status', 'active')
            ->count();

        return [
            'count' => $count,
            'records' => [],
            'summary' => null,
        ];
    }

    public function count(array $parameters): array
    {
        return $this->totalTeachers($parameters);
    }

    public function search(array $parameters): array
    {
        $query = Teacher::query()
            ->where('school_id', $this->schoolContext->id());

        if (!empty($parameters['name'])) {
            $name = (string) $parameters['name'];
            $query->where(function ($q) use ($name) {
                $q->where('first_name', 'like', "%{$name}%")
                    ->orWhere('last_name', 'like', "%{$name}%")
                    ->orWhere('middle_name', 'like', "%{$name}%")
                    ->orWhereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$name}%"]);
            });
        }

        if (!empty($parameters['subject_id'])) {
            $query->whereHas('subjects', fn ($q) => $q->where('subject_id', $parameters['subject_id']));
        }

        $limit = max(1, min((int) ($parameters['limit'] ?? 25), self::MAX_RESULTS));

        $teachers = $query
            ->orderBy('first_name')
            ->limit($limit)
            ->get();

        return [
            'count' => $teachers->count(),
            'records' => $teachers->map(fn (Teacher $t) => [
                'id' => $t->id,
                'name' => $t->full_name,
                'employee_id' => $t->employee_id,
                'email' => $t->email,
                'phone' => $t->phone,
                'subject' => $t->subjects->pluck('name')->implode(', '),
                'status' => $t->status,
            ])->all(),
            'summary' => null,
        ];
    }

    public function onLeave(array $parameters): array
    {
        $schoolId = $this->schoolContext->id();
        $date = $parameters['date'] ?? now()->format('Y-m-d');

        $limit = max(1, min((int) ($parameters['limit'] ?? 50), self::MAX_RESULTS));

        $teacherIdsOnLeave = TeacherLeave::query()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('status', 'approved')
            ->pluck('teacher_id');

        $absentIds = TeacherAttendance::query()
            ->where('attendance_date', $date)
            ->whereIn('status', ['absent', 'half_day'])
            ->pluck('teacher_id');

        $teacherIds = $teacherIdsOnLeave
            ->merge($absentIds)
            ->unique()
            ->values()
            ->all();

        if (empty($teacherIds)) {
            return [
                'count' => 0,
                'records' => [],
                'summary' => ['date' => $date],
            ];
        }

        $teachers = Teacher::query()
            ->where('school_id', $schoolId)
            ->whereIn('id', $teacherIds)
            ->orderBy('first_name')
            ->limit($limit)
            ->get();

        return [
            'count' => $teachers->count(),
            'records' => $teachers->map(fn (Teacher $t) => [
                'id' => $t->id,
                'name' => $t->full_name,
                'employee_id' => $t->employee_id,
                'email' => $t->email,
                'status' => $t->status,
            ])->all(),
            'summary' => ['date' => $date],
        ];
    }
}
