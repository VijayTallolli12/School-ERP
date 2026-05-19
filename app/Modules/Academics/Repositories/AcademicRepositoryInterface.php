<?php

namespace App\Modules\Academics\Repositories;

use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\ClassSubject;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Academics\Models\Subject;
use Illuminate\Database\Eloquent\Builder;

interface AcademicRepositoryInterface
{
    public function academicYears(): Builder;

    public function classes(): Builder;

    public function sections(): Builder;

    public function subjects(): Builder;

    public function classSections(): Builder;

    public function classSubjects(): Builder;

    public function createAcademicYear(array $data): AcademicYear;

    public function updateAcademicYear(AcademicYear $academicYear, array $data): AcademicYear;

    public function createClass(array $data): SchoolClass;

    public function updateClass(SchoolClass $class, array $data): SchoolClass;

    public function createSection(array $data): Section;

    public function updateSection(Section $section, array $data): Section;

    public function createSubject(array $data): Subject;

    public function updateSubject(Subject $subject, array $data): Subject;

    public function createClassSection(array $data): ClassSection;

    public function updateClassSection(ClassSection $classSection, array $data): ClassSection;

    public function deleteClassSection(ClassSection $classSection): void;

    public function createClassSubject(array $data): ClassSubject;

    public function updateClassSubject(ClassSubject $classSubject, array $data): ClassSubject;
}
