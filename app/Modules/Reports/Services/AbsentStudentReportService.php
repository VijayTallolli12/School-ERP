<?php

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Repositories\AbsentStudentReportRepository;
use Carbon\Carbon;

class AbsentStudentReportService
{
    public function __construct(
        protected AbsentStudentReportRepository $repository
    ) {}

    public function getReportData(array $filters = []): array
    {
        $records = $this->repository->getAttendanceRecords($filters);

        $result = [];
        $consecutiveCounts = [];

        foreach ($records as $att) {
            $sid = $att->student_id;

            if ($att->status === 'absent') {
                $consecutiveCounts[$sid] = ($consecutiveCounts[$sid] ?? 0) + 1;
            } else {
                $consecutiveCounts[$sid] = 0;
                continue;
            }

            $student = $att->student;
            $primaryGuardian = $student?->guardians?->first();

            $result[] = [
                'student_name' => optional($student?->user)->full_name ?? optional($student)->full_name ?? 'N/A',
                'admission_no' => $student?->admission_no ?? 'N/A',
                'class_section' => optional($att->classSection)->display_name ?? 'N/A',
                'parent_name' => $primaryGuardian?->name ?? 'N/A',
                'parent_mobile' => $primaryGuardian?->phone ?? 'N/A',
                'attendance_date' => $att->attendance_date instanceof Carbon
                    ? $att->attendance_date->format('Y-m-d')
                    : (string) $att->attendance_date,
                'status' => 'Absent',
                'consecutive_days' => $consecutiveCounts[$sid],
            ];
        }

        return $result;
    }

    public function getSummary(array $filters = []): array
    {
        return $this->repository->getSummary($filters);
    }

    public function getClassWiseChartData(array $filters = []): array
    {
        return $this->repository->getClassWiseAbsenceData($filters);
    }

    public function getTrendChartData(array $filters = []): array
    {
        return $this->repository->getTrendData($filters);
    }

    public function getStudentsByClass(?int $classSectionId, ?int $schoolId): \Illuminate\Support\Collection
    {
        return $this->repository->getStudentsByClass($classSectionId, $schoolId);
    }

    public function getTodayAbsentCount(?int $schoolId): array
    {
        $today = Carbon::today()->toDateString();
        $summary = $this->repository->getSummary([
            'school_id' => $schoolId,
            'from_date' => $today,
            'to_date' => $today,
        ]);
        return $summary;
    }
}
