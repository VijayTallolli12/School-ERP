<?php

namespace App\Modules\Students\Services;

use App\Models\AcademicYear;
use App\Models\School;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentSession;

/**
 * Immutable result of resolving an authenticated user to a Student.
 */
class StudentContext
{
    public function __construct(
        public readonly Student $student,
        public readonly ?StudentSession $session,
        public readonly ?AcademicYear $academicYear,
        public readonly ?School $school,
    ) {}
}
