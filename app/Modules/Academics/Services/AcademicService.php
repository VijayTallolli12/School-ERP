<?php

namespace App\Modules\Academics\Services;

use App\Core\Tenant\SchoolContext;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\ClassSubject;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Repositories\AcademicRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AcademicService
{
    public function __construct(private readonly AcademicRepositoryInterface $academics)
    {
    }

    public function createAcademicYear(array $data): AcademicYear
    {
        return DB::transaction(function () use ($data): AcademicYear {
            $data['school_id'] = app(SchoolContext::class)->id();

            if (! empty($data['is_active'])) {
                AcademicYear::query()->where('school_id', $data['school_id'])->update(['is_active' => false]);
            }

            $year = $this->academics->createAcademicYear($data);
            activity()->causedBy(auth()->user())->performedOn($year)->event('created')->log('Academic year created');

            return $year;
        });
    }

    public function updateAcademicYear(AcademicYear $academicYear, array $data): AcademicYear
    {
        return DB::transaction(function () use ($academicYear, $data): AcademicYear {
            if (! empty($data['is_active'])) {
                AcademicYear::query()
                    ->where('school_id', $academicYear->school_id)
                    ->whereKeyNot($academicYear->id)
                    ->update(['is_active' => false]);
            }

            $year = $this->academics->updateAcademicYear($academicYear, $data);
            activity()->causedBy(auth()->user())->performedOn($year)->event('updated')->log('Academic year updated');

            return $year;
        });
    }

    public function createClass(array $data): SchoolClass
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $class = $this->academics->createClass($data);
        activity()->causedBy(auth()->user())->performedOn($class)->event('created')->log('Class created');

        return $class;
    }

    public function updateClass(SchoolClass $class, array $data): SchoolClass
    {
        $class = $this->academics->updateClass($class, $data);
        activity()->causedBy(auth()->user())->performedOn($class)->event('updated')->log('Class updated');

        return $class;
    }

    public function createSection(array $data): Section
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $section = $this->academics->createSection($data);
        activity()->causedBy(auth()->user())->performedOn($section)->event('created')->log('Section created');

        return $section;
    }

    public function updateSection(Section $section, array $data): Section
    {
        $section = $this->academics->updateSection($section, $data);
        activity()->causedBy(auth()->user())->performedOn($section)->event('updated')->log('Section updated');

        return $section;
    }

    public function createSubject(array $data): Subject
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $subject = $this->academics->createSubject($data);
        activity()->causedBy(auth()->user())->performedOn($subject)->event('created')->log('Subject created');

        return $subject;
    }

    public function updateSubject(Subject $subject, array $data): Subject
    {
        $subject = $this->academics->updateSubject($subject, $data);
        activity()->causedBy(auth()->user())->performedOn($subject)->event('updated')->log('Subject updated');

        return $subject;
    }

    public function createClassSection(array $data): ClassSection
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $classSection = $this->academics->createClassSection($data);
        activity()->causedBy(auth()->user())->performedOn($classSection)->event('created')->log('Class section created');

        return $classSection;
    }

    public function updateClassSection(ClassSection $classSection, array $data): ClassSection
    {
        $classSection = $this->academics->updateClassSection($classSection, $data);
        activity()->causedBy(auth()->user())->performedOn($classSection)->event('updated')->log('Class section updated');

        return $classSection;
    }

    public function deleteClassSection(ClassSection $classSection): void
    {
        $this->academics->deleteClassSection($classSection);
        activity()->causedBy(auth()->user())->performedOn($classSection)->event('deleted')->log('Class section deleted');
    }

    public function assignSubject(array $data): ClassSubject
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $classSubject = $this->academics->createClassSubject($data);
        activity()->causedBy(auth()->user())->performedOn($classSubject)->event('created')->log('Subject assigned to class');

        return $classSubject;
    }
}
