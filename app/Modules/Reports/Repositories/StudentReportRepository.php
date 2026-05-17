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
            class_section.school_class_id,
            school_classes.name as class_name
        ')
        ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
        ->join('class_section', 'student_sessions.class_section_id', '=', 'class_section.id')
        ->join('school_classes', 'class_section.school_class_id', '=', 'school_classes.id')
        ->where('students.school_id', auth()->user()->school_id)
        ->groupBy('class_section.school_class_id', 'school_classes.name');

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('students.admission_date', [
                Carbon::parse($filters['start_date'])->startOfDay(),
                Carbon::parse($filters['end_date'])->endOfDay()
            ]);
        }

        return $query->get();
    }

    public function getClassWiseReportData($filters = [])
    {
        $query = Student::selectRaw('
            COUNT(*) as total_students,
            SUM(CASE WHEN students.gender = "male" THEN 1 ELSE 0 END) as male_count,
            SUM(CASE WHEN students.gender = "female" THEN 1 ELSE 0 END) as female_count,
            SUM(CASE WHEN students.deleted_at IS NULL THEN 1 ELSE 0 END) as active_count,
            SUM(CASE WHEN students.deleted_at IS NOT NULL THEN 1 ELSE 0 END) as inactive_count,
            class_section.school_class_id,
            school_classes.name as class_name
        ')
        ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
        ->join('class_section', 'student_sessions.class_section_id', '=', 'class_section.id')
        ->join('school_classes', 'class_section.school_class_id', '=', 'school_classes.id')
        ->where('students.school_id', auth()->user()->school_id)
        ->groupBy('class_section.school_class_id', 'school_classes.name');

        if (!empty($filters['academic_year_id'])) {
            $query->where('student_sessions.academic_year_id', $filters['academic_year_id']);
        }

        return $query->get();
    }
}