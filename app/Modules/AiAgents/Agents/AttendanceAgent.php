<?php

namespace App\Modules\AiAgents\Agents;

use App\Core\Tenant\SchoolContext;
use App\Models\AcademicYear;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Notifications\Models\Notification;
use Illuminate\Support\Facades\DB;

class AttendanceAgent implements AgentInterface
{
    public function __construct(
        private readonly SchoolContext $schoolContext,
    ) {}

    public function name(): string
    {
        return 'attendance';
    }

    public function description(): string
    {
        return 'Loads today\'s attendance, identifies absent students, notifies parents, and generates an absentee report.';
    }

    public function permissions(): array
    {
        return ['attendance.view', 'attendance.notify'];
    }

    public function config(): array
    {
        return [
            'label' => 'Attendance Agent',
            'icon' => 'calendar-check',
            'color' => 'info',
            'tags' => ['Attendance', 'Notifications', 'Report'],
            'params' => [
                'date' => [
                    'label' => 'Attendance Date',
                    'type' => 'date',
                    'default' => now()->format('Y-m-d'),
                ],
            ],
        ];
    }

    public function validateParams(array $params): array
    {
        $date = ($params['date'] ?? now()->format('Y-m-d'));

        return [
            'date' => $date,
        ];
    }

    public function preview(array $params): array
    {
        $date = $params['date'];
        $schoolId = $this->schoolContext->id();

        $activeYear = AcademicYear::query()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        $attendances = Attendance::query()
            ->where('school_id', $schoolId)
            ->where('attendance_date', $date)
            ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
            ->with([
                'student.parents.user',
                'classSection.schoolClass',
                'classSection.section',
            ])
            ->get();

        $totalStudents = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $absentRecords = $attendances->where('status', 'absent');

        $classBreakdown = [];
        foreach ($attendances as $attendance) {
            $classId = $attendance->class_section_id;
            $className = $attendance->classSection?->display_name ?? 'N/A';

            if (!isset($classBreakdown[$classId])) {
                $classBreakdown[$classId] = [
                    'class_id' => $classId,
                    'class_name' => $className,
                    'total' => 0,
                    'present' => 0,
                    'absent' => 0,
                ];
            }

            $classBreakdown[$classId]['total']++;
            if ($attendance->status === 'present') {
                $classBreakdown[$classId]['present']++;
            } elseif ($attendance->status === 'absent') {
                $classBreakdown[$classId]['absent']++;
            }
        }

        $absentStudents = [];
        foreach ($absentRecords as $attendance) {
            $student = $attendance->student;
            if (!$student) {
                continue;
            }

            $absentStudents[] = [
                'student_id' => $student->id,
                'name' => $student->full_name,
                'admission_no' => $student->admission_no,
                'class' => $attendance->classSection?->display_name ?? 'N/A',
                'class_section_id' => $attendance->class_section_id,
                'parents' => $student->parents->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->full_name,
                    'user_id' => $p->user_id,
                ])->toArray(),
            ];
        }

        return [
            'date' => $date,
            'total_students' => $totalStudents,
            'present_count' => $presentCount,
            'absent_count' => $absentRecords->count(),
            'late_count' => $attendances->where('status', 'late')->count(),
            'half_day_count' => $attendances->where('status', 'half_day')->count(),
            'excused_count' => $attendances->where('status', 'excused')->count(),
            'class_breakdown' => array_values($classBreakdown),
            'students' => $absentStudents,
        ];
    }

    public function execute(array $params): array
    {
        $preview = $this->preview($params);
        $now = now();
        $schoolId = $this->schoolContext->id();
        $userId = auth()->id();
        $results = [];
        $notificationsCreated = 0;

        DB::beginTransaction();
        try {
            foreach ($preview['students'] as $student) {
                $notificationText = $this->generateAbsenteeMessage($student, $preview['date']);

                $notification = Notification::create([
                    'school_id' => $schoolId,
                    'title' => "Absent Alert - {$student['name']}",
                    'message' => $notificationText,
                    'type' => 'attendance_alert',
                    'priority' => 'high',
                    'status' => 'sent',
                    'target_type' => 'parents',
                    'channel' => 'in_app',
                    'sent_at' => $now,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);

                $pivotData = [];
                foreach ($student['parents'] as $parent) {
                    if ($parent['user_id']) {
                        $pivotData[$parent['user_id']] = [
                            'is_read' => false,
                            'delivery_status' => 'delivered',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if (!empty($pivotData)) {
                    $notification->users()->syncWithoutDetaching($pivotData);
                    $notificationsCreated++;
                }

                $results[] = [
                    'student_id' => $student['student_id'],
                    'name' => $student['name'],
                    'class' => $student['class'],
                    'notification_status' => !empty($pivotData) ? 'sent' : 'no_parents',
                ];
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'date' => $params['date'],
            'total_students' => $preview['total_students'],
            'present_count' => $preview['present_count'],
            'absent_count' => $preview['absent_count'],
            'class_breakdown' => $preview['class_breakdown'],
            'results' => $results,
            'notifications_created' => $notificationsCreated,
            'records_processed' => count($results),
        ];
    }

    private function generateAbsenteeMessage(array $student, string $date): string
    {
        $name = $student['name'];
        $class = $student['class'] ?? 'N/A';
        $admissionNo = $student['admission_no'] ?? 'N/A';
        return "Your child {$name} ({$class}, Adm No: {$admissionNo}) was marked absent on {$date}.\n\nPlease contact the school if this is incorrect.\n\nThank you,\nSchool ERP";
    }
}
