<?php

namespace App\Modules\Academics\Repositories;

use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\ClassSubject;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Academics\Models\Subject;
use Illuminate\Database\Eloquent\Builder;

class AcademicRepository implements AcademicRepositoryInterface
{
    public function academicYears(): Builder
    {
        return AcademicYear::query()->withCount('terms')->orderByDesc('starts_on');
    }

    public function classes(): Builder
    {
        return SchoolClass::query()->withCount(['sections', 'classSubjects'])->orderBy('sort_order')->orderBy('name');
    }

    public function sections(): Builder
    {
        return Section::query()->withCount('classes')->orderBy('name');
    }

    public function subjects(): Builder
    {
        return Subject::query()->withCount('classSubjects')->orderBy('name');
    }

    public function classSections(): Builder
    {
        return ClassSection::query()->with(['schoolClass', 'section', 'classTeacher'])->orderBy('id');
    }

    public function classSubjects(): Builder
    {
        return ClassSubject::query()->with(['academicYear', 'schoolClass', 'subject', 'teacher'])->latest('class_subjects.created_at');
    }

    public function createAcademicYear(array $data): AcademicYear
    {
        return AcademicYear::query()->create($data);
    }

    public function updateAcademicYear(AcademicYear $academicYear, array $data): AcademicYear
    {
        $academicYear->fill($data)->save();

        return $academicYear->refresh();
    }

    public function createClass(array $data): SchoolClass
    {
        return SchoolClass::query()->create($data);
    }

    public function updateClass(SchoolClass $class, array $data): SchoolClass
    {
        $class->fill($data)->save();

        return $class->refresh();
    }

    public function createSection(array $data): Section
    {
        return Section::query()->create($data);
    }

    public function updateSection(Section $section, array $data): Section
    {
        $section->fill($data)->save();

        return $section->refresh();
    }

    public function createSubject(array $data): Subject
    {
        return Subject::query()->create($data);
    }

    public function updateSubject(Subject $subject, array $data): Subject
    {
        $subject->fill($data)->save();

        return $subject->refresh();
    }

    public function createClassSection(array $data): ClassSection
    {
        return ClassSection::query()->create($data);
    }

    public function updateClassSection(ClassSection $classSection, array $data): ClassSection
    {
        $classSection->fill($data)->save();

        return $classSection->refresh();
    }

    public function deleteClassSection(ClassSection $classSection): void
    {
        $classSection->delete();
    }

    public function createClassSubject(array $data): ClassSubject
    {
        return ClassSubject::query()->create($data);
    }
}
