<?php

namespace App\Modules\AiAssistant\Services;

use App\Core\Tenant\SchoolContext;
use App\Models\User;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use App\Modules\Teachers\Models\Teacher;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RoleDataScoper
{
    public function __construct(
        private readonly SchoolContext $schoolContext
    ) {}

    public function getRolePermissions(User $user): array
    {
        $role = $user->roles->first()?->name;

        if (!$role) {
            return [];
        }

        return config("ai.role_permissions.{$role}", []);
    }

    public function isIntentAllowed(User $user, string $intent): bool
    {
        $patterns = $this->getRolePermissions($user);

        if (in_array('*', $patterns, true)) {
            return true;
        }

        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $intent)) {
                return true;
            }
        }

        return false;
    }

    public function getScopeFilters(User $user): array
    {
        $role = $user->roles->first()?->name;

        if (!$role) {
            return [];
        }

        return match ($role) {
            'Teacher' => $this->getTeacherScope($user),
            'Parent' => $this->getParentScope($user),
            'Student' => $this->getStudentScope($user),
            default => [],
        };
    }

    public function getErrorMessage(User $user): string
    {
        $role = $user->roles->first()?->name ?? 'User';

        $messages = [
            'Teacher' => 'Teachers can only ask questions about their classes, students, attendance, homework, and exams.',
            'Parent' => 'Parents can only ask about their children\'s attendance, fees, exams, homework, and school summaries.',
            'Student' => 'Students can only ask about their own attendance, exams, homework, and school summaries.',
            'Librarian' => 'Librarians can only ask about library operations and school summaries.',
            'Accountant' => 'Accountants can only ask about fees, students, attendance, and school summaries.',
            'Staff' => 'Staff members can only ask about attendance and school summaries.',
            'Receptionist' => 'Receptionists can only ask about student records.',
        ];

        return $messages[$role] ?? "You are not authorized to perform this action.";
    }

    private function getTeacherScope(User $user): array
    {
        $teacher = Teacher::query()->where('user_id', $user->getKey())->first();

        if (!$teacher) {
            return [];
        }

        $classSectionIds = $teacher->classSections()->pluck('class_section_id')->toArray();

        return [
            'class_section_ids' => $classSectionIds,
            'teacher_id' => $teacher->id,
        ];
    }

    private function getParentScope(User $user): array
    {
        $guardian = Guardian::query()->where('user_id', $user->getKey())->first();

        if (!$guardian) {
            return [];
        }

        $studentIds = $guardian->students()->pluck('student_id')->toArray();

        return [
            'student_ids' => $studentIds,
        ];
    }

    private function getStudentScope(User $user): array
    {
        $student = Student::query()->where('user_id', $user->getKey())->first();

        if (!$student) {
            return [];
        }

        return [
            'student_id' => $student->id,
        ];
    }
}
