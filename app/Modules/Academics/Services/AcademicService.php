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
use App\Modules\Teachers\Models\Teacher;
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
        return DB::transaction(function () use ($data): ClassSubject {
            $data['school_id'] = app(SchoolContext::class)->id();
            $classSubject = $this->academics->createClassSubject($data);
            $this->syncTeacherPivots($classSubject);
            activity()->causedBy(auth()->user())->performedOn($classSubject)->event('created')->log('Subject assigned to class');

            return $classSubject;
        });
    }

    public function updateClassSubject(ClassSubject $classSubject, array $data): ClassSubject
    {
        return DB::transaction(function () use ($classSubject, $data): ClassSubject {
            $oldTeacherId = $classSubject->teacher_id;
            $oldClassId = $classSubject->class_id;
            $oldSubjectId = $classSubject->subject_id;

            $classSubject = $this->academics->updateClassSubject($classSubject, $data);

            // If teacher changed or class/subject changed, clean up old teacher pivots
            if ($oldTeacherId && ($oldTeacherId !== $classSubject->teacher_id || $oldClassId !== $classSubject->class_id || $oldSubjectId !== $classSubject->subject_id)) {
                $this->detachOldTeacherPivot($oldTeacherId, $oldSubjectId, $oldClassId);
            }

            $this->syncTeacherPivots($classSubject);
            activity()->causedBy(auth()->user())->performedOn($classSubject)->event('updated')->log('Class subject updated');

            return $classSubject;
        });
    }

    public function deleteClassSubject(ClassSubject $classSubject): void
    {
        DB::transaction(function () use ($classSubject): void {
            $this->cleanupTeacherPivots($classSubject);
            $classSubject->delete();
            activity()->causedBy(auth()->user())->performedOn($classSubject)->event('deleted')->log('Class subject removed');
        });
    }

    /**
     * Sync teacher_subject and teacher_class_section pivots from a ClassSubject record.
     * ClassSubject->teacher_id is a user_id — resolve to Teacher model via the teachers table.
     */
    private function syncTeacherPivots(ClassSubject $classSubject): void
    {
        if (! $classSubject->teacher_id) {
            return;
        }

        $teacher = Teacher::query()->where('user_id', $classSubject->teacher_id)->first();
        if (! $teacher) {
            return;
        }

        $schoolId = app(SchoolContext::class)->id();

        // Sync teacher_subject pivot (without detaching other subjects)
        $teacher->subjects()->syncWithoutDetaching([
            $classSubject->subject_id => ['school_id' => $schoolId],
        ]);

        // Find the class_section matching this class to sync teacher_class_section pivot
        $classSection = \App\Modules\Academics\Models\ClassSection::query()
            ->where('class_id', $classSubject->class_id)
            ->first();

        if ($classSection) {
            $teacher->classSections()->syncWithoutDetaching([
                $classSection->id => ['school_id' => $schoolId, 'is_class_teacher' => false],
            ]);
        }
    }

    /**
     * Clean up teacher pivots when a ClassSubject is deleted.
     * Only remove if no other class_subject exists for the same teacher+subject or teacher+class.
     */
    private function cleanupTeacherPivots(ClassSubject $classSubject): void
    {
        if (! $classSubject->teacher_id) {
            return;
        }

        $teacher = Teacher::query()->where('user_id', $classSubject->teacher_id)->first();
        if (! $teacher) {
            return;
        }

        // Detach subject only if no other active class_subject links this teacher to this subject
        $otherSubjectCount = ClassSubject::query()
            ->where('teacher_id', $classSubject->teacher_id)
            ->where('subject_id', $classSubject->subject_id)
            ->whereKeyNot($classSubject->id)
            ->count();

        if ($otherSubjectCount === 0) {
            $teacher->subjects()->detach($classSubject->subject_id);
        }

        // Detach class_section only if no other active class_subject links this teacher to this class
        $otherClassCount = ClassSubject::query()
            ->where('teacher_id', $classSubject->teacher_id)
            ->where('class_id', $classSubject->class_id)
            ->whereKeyNot($classSubject->id)
            ->count();

        if ($otherClassCount === 0) {
            $classSection = \App\Modules\Academics\Models\ClassSection::query()
                ->where('class_id', $classSubject->class_id)
                ->first();

            if ($classSection) {
                $teacher->classSections()->detach($classSection->id);
            }
        }
    }

    /**
     * Detach old teacher pivots when a ClassSubject's teacher, subject, or class changes during update.
     * Only remove if no other class_subject still links the old teacher to the old subject/class.
     */
    private function detachOldTeacherPivot(string|int|null $oldTeacherId, string|int|null $oldSubjectId, string|int|null $oldClassId): void
    {
        if (! $oldTeacherId) {
            return;
        }

        $teacher = Teacher::query()->where('user_id', $oldTeacherId)->first();
        if (! $teacher) {
            return;
        }

        // Detach subject only if no other class_subject links this teacher to this subject
        if ($oldSubjectId) {
            $otherSubjectCount = ClassSubject::query()
                ->where('teacher_id', $oldTeacherId)
                ->where('subject_id', $oldSubjectId)
                ->count();

            if ($otherSubjectCount === 0) {
                $teacher->subjects()->detach($oldSubjectId);
            }
        }

        // Detach class_section only if no other class_subject links this teacher to this class
        if ($oldClassId) {
            $otherClassCount = ClassSubject::query()
                ->where('teacher_id', $oldTeacherId)
                ->where('class_id', $oldClassId)
                ->count();

            if ($otherClassCount === 0) {
                $classSection = \App\Modules\Academics\Models\ClassSection::query()
                    ->where('class_id', $oldClassId)
                    ->first();

                if ($classSection) {
                    $teacher->classSections()->detach($classSection->id);
                }
            }
        }
    }
}
