<?php

namespace App\Listeners;

use App\Core\Tenant\SchoolContext;
use App\Events\AgentExecutionCompleted;
use App\Events\AttendanceMarked;
use App\Events\ExamPublished;
use App\Events\FeeReminderGenerated;
use App\Events\HomeworkAssigned;
use App\Events\TeacherAttendanceMarked;
use App\Models\User;
use App\Modules\Notifications\Models\Notification;
use Illuminate\Support\Facades\Log;

class CreateDatabaseNotification
{
    public function handle(AttendanceMarked|HomeworkAssigned|ExamPublished|FeeReminderGenerated|AgentExecutionCompleted|TeacherAttendanceMarked $event): void
    {
        try {
            [$title, $message, $type, $targetUserIds] = match ($event::class) {
                AttendanceMarked::class => [
                    'Attendance Updated',
                    $this->formatAttendanceMessage($event),
                    'attendance_alert',
                    $this->resolveStudentAndParentUserIds($event->studentId),
                ],
                TeacherAttendanceMarked::class => [
                    'Attendance Updated',
                    $this->formatTeacherAttendanceMessage($event),
                    'attendance_alert',
                    $this->resolveTeacherUserIds($event->teacherId),
                ],
                HomeworkAssigned::class => [
                    'New Homework',
                    "New homework assigned: {$event->title}.",
                    'announcement',
                    $this->resolveClassStudentUserIds($event->classSectionId),
                ],
                ExamPublished::class => [
                    'Exam Published',
                    "Exam published: {$event->examName}.",
                    'exam_result_alert',
                    $this->resolveClassStudentUserIds($event->classSectionId),
                ],
                FeeReminderGenerated::class => [
                    'Fee Reminder',
                    "Fee payment of {$event->amountDue} is due.",
                    'fee_reminder',
                    [$event->parentUserId],
                ],
                AgentExecutionCompleted::class => [
                    'Agent Execution Completed',
                    "Agent '{$event->agentName}' completed with status: {$event->status}.",
                    'announcement',
                    [],
                ],
            };

            if (empty($targetUserIds)) {
                Log::info('No target users for notification', ['event' => $event::class]);
                return;
            }

            $notification = Notification::query()->create([
                'school_id' => app(SchoolContext::class)->id(),
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'priority' => 'medium',
                'status' => 'sent',
                'target_type' => 'all',
                'channel' => 'in_app',
                'sent_at' => now(),
                'created_by' => $targetUserIds[0] ?? 1,
                'updated_by' => $targetUserIds[0] ?? 1,
            ]);

            $now = now();
            $pivotData = [];
            foreach ($targetUserIds as $uid) {
                $pivotData[$uid] = [
                    'delivery_status' => 'delivered',
                    'updated_at' => $now,
                ];
            }
            $notification->users()->syncWithoutDetaching($pivotData);
            $notification->update(['sent_at' => $now]);
        } catch (\Throwable $e) {
            Log::error('Failed to create database notification', [
                'event' => $event::class,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function formatAttendanceMessage(AttendanceMarked $event): string
    {
        $name = $event->studentName ?: "Student #{$event->studentId}";
        $time = $event->markedAt ?: now()->format('h:i A');
        return "{$name} marked " . strtoupper($event->status) . " at {$time}.";
    }

    private function formatTeacherAttendanceMessage(TeacherAttendanceMarked $event): string
    {
        $name = $event->teacherName ?: "Teacher #{$event->teacherId}";
        $time = $event->markedAt ?: now()->format('h:i A');
        return "{$name} marked " . strtoupper($event->status) . " at {$time}.";
    }

    private function resolveStudentAndParentUserIds(int $studentId): array
    {
        $student = \App\Modules\Students\Models\Student::with('parents.user')->find($studentId);
        if (! $student) {
            return [];
        }

        $userIds = [];

        if ($student->user_id) {
            $userIds[] = $student->user_id;
        }

        foreach ($student->parents as $guardian) {
            if ($guardian->user_id) {
                $userIds[] = $guardian->user_id;
            }
        }

        return array_values(array_unique($userIds));
    }

    private function resolveTeacherUserIds(int $teacherId): array
    {
        return User::query()
            ->whereHas('teacher', fn ($q) => $q->where('id', $teacherId))
            ->pluck('id')
            ->all();
    }

    private function resolveClassStudentUserIds(int $classSectionId): array
    {
        return User::query()
            ->whereHas('student.sessions', fn ($q) => $q
                ->where('class_section_id', $classSectionId)
                ->where('status', 'active')
            )
            ->pluck('id')
            ->all();
    }
}
