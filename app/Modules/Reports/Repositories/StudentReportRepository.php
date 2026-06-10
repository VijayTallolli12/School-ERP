<?php

namespace App\Modules\Reports\Repositories;

use App\Modules\Students\Models\Student;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Academics\Models\ClassSection;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class StudentReportRepository implements StudentReportRepositoryInterface
{
    public function getStudentListQuery($filters = [])
    {
        $query = Student::with([
            'user',
            'guardians',
            'parents',
            'sessions.academicYear',
            'sessions.classSection.schoolClass',
            'sessions.classSection.section'
        ])->whereHas('sessions', function ($q) use ($filters) {
            if (!empty($filters['academic_year_id'])) {
                $q->where('academic_year_id', $filters['academic_year_id']);
            }
            if (!empty($filters['class_section_id'])) {
                $q->where('class_section_id', $filters['class_section_id']);
            }
        });

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($filters['status'] === 'inactive') {
                $query->onlyTrashed();
            }
        }

        return $query;
    }

    public function getAdmissionReportData($filters = [])
    {
        $query = Student::selectRaw('
            COUNT(*) as total_admissions,
            class_section.class_id,
            classes.name as class_name
        ')
        ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
        ->join('class_section', 'student_sessions.class_section_id', '=', 'class_section.id')
        ->join('classes', 'class_section.class_id', '=', 'classes.id')
        ->where('students.school_id', auth()->user()->school_id)
        ->groupBy('class_section.class_id', 'classes.name');

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('students.admission_date', [
                Carbon::parse($filters['start_date'])->startOfDay(),
                Carbon::parse($filters['end_date'])->endOfDay()
            ]);
        }

        return $query->get();
    }

    public function getGenderWiseData($filters = [])
    {
        $schoolId = auth()->user()->school_id;

        $query = Student::selectRaw("
            classes.id as class_id,
            classes.name as class_name,
            COUNT(*) as total,
            SUM(CASE WHEN students.gender = 'male' THEN 1 ELSE 0 END) as male,
            SUM(CASE WHEN students.gender = 'female' THEN 1 ELSE 0 END) as female,
            SUM(CASE WHEN students.gender NOT IN ('male','female') OR students.gender IS NULL THEN 1 ELSE 0 END) as other
        ")
        ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
        ->join('class_section', 'student_sessions.class_section_id', '=', 'class_section.id')
        ->join('classes', 'class_section.class_id', '=', 'classes.id')
        ->where('students.school_id', $schoolId)
        ->groupBy('classes.id', 'classes.name')
        ->orderBy('classes.name');

        if (!empty($filters['academic_year_id'])) {
            $query->where('student_sessions.academic_year_id', $filters['academic_year_id']);
        }
        if (!empty($filters['class_section_id'])) {
            $query->where('student_sessions.class_section_id', $filters['class_section_id']);
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('students.admission_date', [
                Carbon::parse($filters['start_date'])->startOfDay(),
                Carbon::parse($filters['end_date'])->endOfDay(),
            ]);
        }

        return $query->get();
    }

    public function getDirectoryQuery($filters = [])
    {
        $schoolId = auth()->user()->school_id;

        $query = Student::with([
            'user',
            'guardians',
            'parents',
            'sessions' => fn($q) => $q->where('status', 'active'),
            'sessions.classSection.schoolClass',
            'sessions.classSection.section',
        ])->where('school_id', $schoolId);

        if (!empty($filters['academic_year_id'])) {
            $query->whereHas('sessions', fn($q) => $q->where('academic_year_id', $filters['academic_year_id']));
        }
        if (!empty($filters['class_section_id'])) {
            $query->whereHas('sessions', fn($q) => $q->where('class_section_id', $filters['class_section_id']));
        }
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('admission_date', [
                Carbon::parse($filters['start_date'])->startOfDay(),
                Carbon::parse($filters['end_date'])->endOfDay(),
            ]);
        }

        // Status filter (soft delete)
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($filters['status'] === 'inactive') {
                $query->onlyTrashed();
            }
        }

        return $query;
    }

    public function getClassWiseReportData($filters = [])
    {
        $query = Student::selectRaw('
            COUNT(*) as total_students,
            SUM(CASE WHEN students.gender = "male" THEN 1 ELSE 0 END) as male_count,
            SUM(CASE WHEN students.gender = "female" THEN 1 ELSE 0 END) as female_count,
            SUM(CASE WHEN students.deleted_at IS NULL THEN 1 ELSE 0 END) as active_count,
            SUM(CASE WHEN students.deleted_at IS NOT NULL THEN 1 ELSE 0 END) as inactive_count,
            class_section.class_id,
            classes.name as class_name
        ')
        ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
        ->join('class_section', 'student_sessions.class_section_id', '=', 'class_section.id')
        ->join('classes', 'class_section.class_id', '=', 'classes.id')
        ->where('students.school_id', auth()->user()->school_id)
        ->groupBy('class_section.class_id', 'classes.name');

        if (!empty($filters['academic_year_id'])) {
            $query->where('student_sessions.academic_year_id', $filters['academic_year_id']);
        }

        return $query->get();
    }
}