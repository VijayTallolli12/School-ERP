<?php

namespace App\Modules\Students\Services;

use App\Core\Tenant\SchoolContext;
use App\Http\Middleware\SetSchoolContext;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Modules\Students\Exceptions\StudentLinkageException;
use App\Modules\Students\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;

/**
 * Resolves an authenticated user to a full Student context and applies the
 * school/permission context required by the rest of the request.
 *
 * Resolution chain:
 *   User → Student → Active Session → Class → Section → School → Permissions
 *
 * Self-healing:
 *   - A broken/absent students.user_id link is repaired when exactly one
 *     unambiguous candidate exists in the user's school (role + name match).
 *   - A missing school_user pivot row is restored.
 *
 * Failures raise StudentLinkageException (a typed HttpException) so callers can
 * return a meaningful 404/403 JSON response instead of a bare 500.
 */
class StudentAuthService
{
    public function resolveForRequest(User $user): StudentContext
    {
        return $this->resolve($user);
    }

    public function resolveForLogin(User $user, ?Request $request = null): StudentContext
    {
        return $this->resolve($user);
    }

    private function resolve(User $user): StudentContext
    {
        $students = $user->student()->withTrashed()->get();

        if ($students->count() === 1) {
            $student = $students->first();

            $this->assertUsable($student);

            $schoolId = $student->school_id;

            $this->applyContext($user, $schoolId);
            $this->repairSchoolPivot($user, $schoolId);

            return $this->context($student);
        }

        if ($students->count() > 1) {
            throw StudentLinkageException::ambiguous();
        }

        // No direct link — attempt a safe self-heal.
        $repaired = $this->repairAbsentLinkage($user);

        if ($repaired) {
            $this->applyContext($user, $repaired->school_id);
            $this->repairSchoolPivot($user, $repaired->school_id);

            return $this->context($repaired->fresh());
        }

        throw StudentLinkageException::notLinked();
    }

    private function assertUsable(Student $student): void
    {
        if ($student->trashed()) {
            throw StudentLinkageException::archived();
        }

        if ($student->status !== 'active') {
            throw StudentLinkageException::inactive($student->full_name);
        }
    }

    private function context(Student $student): StudentContext
    {
        $student->load('sessions.classSection.schoolClass', 'sessions.classSection.section', 'sessions.academicYear');

        $session = $student->sessions->firstWhere('status', 'active');

        $academicYear = $session?->academicYear
            ?? AcademicYear::query()
                ->where('school_id', $student->school_id)
                ->where('is_active', true)
                ->first();

        $school = $student->school ?? School::query()->find($student->school_id);

        return new StudentContext(
            student: $student,
            session: $session,
            academicYear: $academicYear,
            school: $school,
        );
    }

    /**
     * Repair a broken students.user_id link when the match is unambiguous:
     * the user must hold the Student role for the school, and exactly one
     * active, unclaimed student record must match by full name in that school.
     */
    private function repairAbsentLinkage(User $user): ?Student
    {
        $schoolId = SetSchoolContext::resolveFromUser($user) ?? $user->current_school_id;

        if (! $schoolId) {
            return null;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
        $user->unsetRelation('roles');

        if (! $user->hasRole('Student')) {
            return null;
        }

        $userName = mb_strtolower(trim((string) $user->name));

        $candidates = Student::query()
            ->where('school_id', $schoolId)
            ->whereNull('user_id')
            ->where('status', 'active')
            ->get()
            ->filter(fn (Student $s) => mb_strtolower(trim($s->full_name)) === $userName);

        if ($candidates->count() === 1) {
            $student = $candidates->first();

            $student->user_id = $user->id;
            $student->save();

            Log::info('Student linkage auto-repaired: user linked to student record.', [
                'user_id' => $user->id,
                'student_id' => $student->id,
                'school_id' => $student->school_id,
            ]);

            return $student->fresh();
        }

        if ($candidates->count() > 1) {
            Log::warning('Student linkage ambiguous: multiple matching student records.', [
                'user_id' => $user->id,
                'school_id' => $schoolId,
                'matches' => $candidates->pluck('id')->all(),
            ]);
        }

        return null;
    }

    private function applyContext(User $user, ?int $schoolId): void
    {
        if (! $schoolId) {
            return;
        }

        app(SchoolContext::class)->set($schoolId);
        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');
    }

    private function repairSchoolPivot(User $user, ?int $schoolId): void
    {
        if (! $schoolId) {
            return;
        }

        if ($user->schools()->whereKey($schoolId)->exists()) {
            return;
        }

        $user->schools()->attach($schoolId, [
            'status' => 'active',
            'is_primary' => true,
        ]);

        Log::info('User attached to school pivot (self-healed).', [
            'user_id' => $user->id,
            'school_id' => $schoolId,
        ]);
    }
}
