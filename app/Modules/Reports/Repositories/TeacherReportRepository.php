<?php

namespace App\Modules\Reports\Repositories;

use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class TeacherReportRepository implements TeacherReportRepositoryInterface
{
    protected function getSchoolId()
    {
        return auth()->user()->school_id ?? null;
    }

    public function dashboardStats(): array
    {
        $schoolId = $this->getSchoolId();

        $totalTeachers = Teacher::where('school_id', $schoolId)->count();
        $activeTeachers = Teacher::where('school_id', $schoolId)->where('status', 'active')->count();
        
        $classTeachers = Teacher::where('school_id', $schoolId)
            ->whereHas('classTeacherSections')
            ->count();

        $subjectAllocationCount = DB::table('teacher_subject')
            ->join('teachers', 'teacher_subject.teacher_id', '=', 'teachers.id')
            ->where('teachers.school_id', $schoolId)
            ->count();

        return [
            'total_teachers' => $totalTeachers,
            'active_teachers' => $activeTeachers,
            'class_teachers' => $classTeachers,
            'subject_allocations' => $subjectAllocationCount,
        ];
    }

    public function teacherList(array $filters = []): array
    {
        $schoolId = $this->getSchoolId();
        
        $query = Teacher::with(['subjects', 'classSections.schoolClass', 'classSections.section'])
            ->where('school_id', $schoolId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['subject_id'])) {
            $query->whereHas('subjects', function ($q) use ($filters) {
                $q->where('subjects.id', $filters['subject_id']);
            });
        }

        if (!empty($filters['class_section_id'])) {
            $query->whereHas('classSections', function ($q) use ($filters) {
                $q->where('class_sections.id', $filters['class_section_id']);
            });
        }

        if (!empty($filters['joining_date_from']) && !empty($filters['joining_date_to'])) {
            $query->whereBetween('joining_date', [$filters['joining_date_from'], $filters['joining_date_to']]);
        }

        return $query->get()->toArray();
    }

    public function attendance(array $filters = []): array
    {
        $schoolId = $this->getSchoolId();

        $query = Teacher::where('school_id', $schoolId);

        $query
            ->when(Arr::get($filters, 'teacher_id'), fn ($q, $teacherId) => $q->where('id', $teacherId))
            ->when(Arr::get($filters, 'status'), fn ($q, $status) => $q->where('status', $status));

        $teachers = $query->get();
        $teacherIds = $teachers->pluck('id')->toArray();

        $attendanceQuery = TeacherAttendance::whereIn('teacher_id', $teacherIds);

        $attendanceQuery
            ->when(Arr::get($filters, 'attendance_status'), fn ($q, $status) => $q->where('status', $status))
            ->when(Arr::get($filters, 'month'), fn ($q, $month) => $q->whereMonth('attendance_date', $month))
            ->when(Arr::get($filters, 'year'), fn ($q, $year) => $q->whereYear('attendance_date', $year))
            ->when(Arr::get($filters, 'from_date'), fn ($q, $date) => $q->whereDate('attendance_date', '>=', $date))
            ->when(Arr::get($filters, 'to_date'), fn ($q, $date) => $q->whereDate('attendance_date', '<=', $date));

        $attendances = $attendanceQuery->get();

        $result = [];
        foreach ($teachers as $teacher) {
            $teacherAttendances = $attendances->where('teacher_id', $teacher->id);
            $present = $teacherAttendances->where('status', 'present')->count();
            $absent = $teacherAttendances->where('status', 'absent')->count();
            $late = $teacherAttendances->where('status', 'late')->count();
            $half_day = $teacherAttendances->where('status', 'half_day')->count();
            $excused = $teacherAttendances->where('status', 'excused')->count();

            $total = $present + $absent + $late + $half_day + $excused;
            
            $presentEquivalent = $present + $late + ($half_day * 0.5);
            $percentage = $total > 0 ? round(($presentEquivalent / $total) * 100, 2) : 0;

            $result[] = [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->full_name,
                'employee_id' => $teacher->employee_id,
                'status' => $teacher->status,
                'teacher' => $teacher->toArray(),
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'half_day' => $half_day,
                'excused' => $excused,
                'total' => $total,
                'percentage' => $percentage
            ];
        }

        return $result;
    }

    public function subjectAllocation(array $filters = []): array
    {
        $schoolId = $this->getSchoolId();

        $query = Teacher::with(['subjects'])
            ->where('school_id', $schoolId)
            ->whereHas('subjects');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['subject_id'])) {
            $query->whereHas('subjects', function ($q) use ($filters) {
                $q->where('subjects.id', $filters['subject_id']);
            });
        }

        return $query->get()->toArray();
    }

    public function classTeacherMapping(array $filters = []): array
    {
        $schoolId = $this->getSchoolId();

        $query = Teacher::with(['classTeacherSections.schoolClass', 'classTeacherSections.section'])
            ->where('school_id', $schoolId)
            ->whereHas('classTeacherSections');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['class_section_id'])) {
            $query->whereHas('classTeacherSections', function ($q) use ($filters) {
                $q->where('class_sections.id', $filters['class_section_id']);
            });
        }

        return $query->get()->toArray();
    }
}
