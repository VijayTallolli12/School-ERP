<?php

namespace App\Modules\Attendance\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Repositories\AttendanceRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        private readonly AttendanceRepositoryInterface $attendances,
        private readonly SchoolContext $schoolContext
    ) {}

    public function markAttendance(array $data): Attendance
    {
        return DB::transaction(function () use ($data): Attendance {
            $attendanceData = [
                'school_id' => $this->schoolContext->id(),
                'student_id' => $data['student_id'],
                'class_section_id' => $data['class_section_id'],
                'academic_year_id' => $data['academic_year_id'],
                'attendance_date' => $data['attendance_date'],
                'status' => $data['status'] ?? 'present',
                'marked_by' => auth()->id(),
                'remarks' => $data['remarks'] ?? null,
            ];

            $attendance = $this->attendances->findByDateAndStudent(
                $data['attendance_date'],
                $data['student_id']
            );

            if ($attendance) {
                $attendance = $this->attendances->update($attendance, $attendanceData);
            } else {
                $attendance = $this->attendances->create($attendanceData);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($attendance)
                ->event('created')
                ->log('Attendance marked: '.$attendance->status_label);

            return $attendance;
        });
    }

    public function bulkMarkAttendance(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $results = [];
            $classSection = $data['class_section_id'];
            $date = $data['attendance_date'];
            $academicYear = $data['academic_year_id'];

            foreach ($data['students'] as $studentId => $status) {
                $attendanceData = [
                    'school_id' => $this->schoolContext->id(),
                    'student_id' => $studentId,
                    'class_section_id' => $classSection,
                    'academic_year_id' => $academicYear,
                    'attendance_date' => $date,
                    'status' => $status,
                    'marked_by' => auth()->id(),
                    'remarks' => ($data['remarks'] ?? [])[$studentId] ?? null,
                ];

                $attendance = $this->attendances->findByDateAndStudent($date, $studentId);

                if ($attendance) {
                    $attendance = $this->attendances->update($attendance, $attendanceData);
                } else {
                    $attendance = $this->attendances->create($attendanceData);
                }

                $results[] = $attendance;

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($attendance)
                    ->event('created')
                    ->log('Bulk attendance marked: '.$attendance->status_label);
            }

            return $results;
        });
    }

    public function update(Attendance $attendance, array $data): Attendance
    {
        return DB::transaction(function () use ($attendance, $data): Attendance {
            $attendanceData = [
                'status' => $data['status'] ?? $attendance->status,
                'remarks' => $data['remarks'] ?? $attendance->remarks,
            ];

            $attendance = $this->attendances->update($attendance, $attendanceData);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($attendance)
                ->event('updated')
                ->log('Attendance updated: '.$attendance->status_label);

            return $attendance;
        });
    }

    public function delete(Attendance $attendance): void
    {
        $this->attendances->delete($attendance);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($attendance)
            ->event('deleted')
            ->log('Attendance deleted');
    }

    public function getMonthlyReport(int $classSectionId, int $month, int $year): array
    {
        return $this->attendances->getMonthlyReport($classSectionId, $month, $year);
    }

    public function getMonthlyReportDetail(int $classSectionId, int $month, int $year): array
    {
        return [
            'summary' => $this->attendances->getMonthlyReport($classSectionId, $month, $year),
            'students' => $this->attendances->getMonthlyStudentBreakdown($classSectionId, $month, $year),
        ];
    }

    public function getStatistics(array $filters): array
    {
        return $this->attendances->getStatistics($filters);
    }
}
